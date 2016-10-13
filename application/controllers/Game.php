<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Game extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->helper('algo');
  }

  public $utoken;

  public $gid;

  public function index(){
 		$this->load->view('basic');
 	}

	/**
	 * [create description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * creates the game
	 * @return void
	 */
	public function create(){
    $gameId = $this->uuid();
    $utoken = $this->input->post('utoken');
    $nick = $this->input->post('nick');
		$obj  = new algo();
		$game = $obj->wordEngine();
		if(!empty($game['status'])){
			$words = $game['words'];
			$matrix = $game['matrix'];
			$info = $game['info'];
			$this->setRedis_create($gameId, $utoken, $nick, $matrix, $words, $info);
	    $this->setHeader(['gameId'=>$gameId, 'utoken'=>$utoken, 'nick'=>$nick]);
		}else{
			$this->setHeader($game, 400);
		}
	}

	/**
	 * [join description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * assign players to the existing games
	 * @return void
	 */
  public function join(){
		/* players, players_nick, scores, */
    $gameId = $this->input->post('gameId');
    $utoken = $this->input->post('utoken');
		$nick = $this->input->post('nick');
		$game = $this->setRedis_info($gameId);
		if(empty($game)){
			$this->setHeader(['registered'=>0, 'player_id'=>$utoken, 'nick'=>$nick, 'msg'=>'Invalid game id!'], 202);
		}else{
			$matrix = $game['matrix'];
			$admin = $game['admin'];

			if(!in_array($utoken, $game['players'])){
				if(count($game['players']) > 5){
					$this->setHeader(['registered'=>0, 'player_id'=>$utoken, 'nick'=>$nick, 'msg'=>'Game is full!'], 202);
				}
				if(in_array($game['status'], ['inPlay', 'completed'])){
					$this->setHeader(['registered'=>0, 'player_id'=>$utoken, 'nick'=>$nick, 'msg'=>'Cannot join...Game status: '.$game['status'].'!'], 202);
				}
				array_push($game['players'], $utoken);
				array_push($game['turn_seq'], $utoken);
				$game['scores'][$utoken] = 0;
				$game['players_nick'][$utoken] = $nick;
				$this->setRedis_set($gameId, $game);
				$this->setHeader(['registered'=>1, 'player_id'=>$utoken, 'nick'=>$nick, 'msg'=>'Succesfully joined the game!'], 200);
			}else{
				$this->setHeader(['registered'=>1, 'player_id'=>$utoken, 'nick'=>$nick, 'msg'=>'Succesfully joined the game!'], 200);
			}
		}

  }

	/**
	 * [start description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * start the game
	 * @return void
	 */
  public function start(){
		$gameId = $this->input->post('gameId');
    $utoken = $this->input->post('utoken');
		$game = $this->setRedis_info($gameId);
		if($game['admin']==$utoken){
			if(count($game['players']) < 2){
				$this->setHeader(['success'=>0, 'msg'=>'Cannot start the game... min. 2 players needed!'], 202);
			}
			$game['status'] = "inPlay";
			$this->setRedis_set($gameId, $game);
			$this->setHeader(['success'=>1, 'msg'=>'Game has been started successfully!'], 200);
		}else{
			$this->setHeader(['success'=>0, 'msg'=>'You are not admin of this game!'], 202);
		}
  }

	/**
	 * [play description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * fulfil the chance of the player
	 * @return void
	 */
  public function play(){
		$gameId = $this->input->post('gameId');
    $utoken = $this->input->post('utoken');
		$word = $this->input->post('word');
		$game = $this->setRedis_info($gameId);
		if(!in_array($utoken, $game['players'])){
			$this->setHeader(['success'=>0, 'msg'=>'You are not a player in this game or game session expired!'], 202);
		}
		if($game['status'] == 'waiting'){
			$this->setHeader(['success'=>0, 'msg'=>'Wait for game to begin...Hold tight!'], 202);
		}
		if($game['status'] == 'completed'){
			$this->setHeader(['success'=>0, 'msg'=>'Game has been completed!'], 202);
		}
		if($utoken != $game['current_player']){
			$this->setHeader(['success'=>0, 'msg'=>'Wait for your turn!'], 202);
		}
		if($word=='#'){
			$game = $this->nextPlayer($game, $utoken);
			$this->setRedis_set($gameId, $game);
			$this->setHeader(['success'=>0, 'msg'=>'You have passed your chance!'], 202);
		}else{
			$obj  = new algo();
			$result = $obj->checkWord($game, $word, $utoken);
			if($result['status']){
				$game = $result['game'];
				$game = $this->nextPlayer($game, $utoken);
				$game = $this->gameStatus($game);
				$this->setRedis_set($gameId, $game);
				$this->setHeader(['success'=>1, 'msg'=>'Congratulations...Successfully matched the word!'], 200);
			}else{
				$game = $this->nextPlayer($game, $utoken);
				$this->setRedis_set($gameId, $game);
				$this->setHeader(['success'=>0, 'msg'=>$result['msg']], 202);
			}
		}
  }

	/**
	 * [gameStatus description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * check the status of the game
	 * @param [array] $game
	 * @return [array]
	 */
	private function gameStatus($game){
		$count = array_diff($game['words'],$game['words_found']);
		if(count($count)==0){
			$game['status'] = 'completed';
		}
		return $game;
	}

	/**
	 * [nextPlayer description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * switch the turns
	 * @param {[array]} $game
	 * @param {[string]} $utoken
	 * @return {[array]} $game
	 */
	private function nextPlayer($game, $utoken){
		$key = array_search ($utoken, $game['turn_seq']);
		if(($key+1) == count($game['turn_seq'])){
			$game['current_player'] = $game['turn_seq'][0];
		}else{
			$game['current_player'] = $game['turn_seq'][$key+1];
		}
		return $game;
	}

	/**
	 * [info description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * provide information related to the game
	 * @return void
	 */
  public function info(){
		$gameId = $this->input->post('gameId');
    $utoken = $this->input->post('utoken');
		$game = $this->setRedis_info($gameId);

		if(empty($game)){
			$this->setHeader('Invalid game id!', 400);
		}else{
			$admin = $game['admin'];
			$players = $game['players'];
			$game_status = $game['status'];
			$scores = $game['scores'];
			$current_player = $game['current_player'];
			$turn_seq = $game['turn_seq'];
			$words_done = $game['words_found'];
			$words = $game['words'];
			$matrix = $game['matrix'];
			$players_nick = $game['players_nick'];
			if(!in_array($utoken, $players)){
				$this->setHeader('You are not a player in this game!', 400);
			}
			$this->setHeader(['game_status'=>$game_status, 'current_player'=>$current_player, 'turn_seq'=>$turn_seq, 'scores'=>$scores,
			 									'words_done'=>$words_done, 'matrix'=>$matrix, 'players'=>$players_nick, 'admin'=>$admin, 'words'=>$words]);
		}

  }

	/**
	 * [uuid description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * generates a unique identifier
	 * @return [string]
	 */
  private function uuid(){
    return uniqid('');
  }

	/**
	 * [auth description]
	 * partially authenticate the user and provide unique identifier
	 * written by Ankit Vaishnav on 2016-10-13
	 */
  public function auth(){
    if(empty($_COOKIE["utoken"])){
      $cookie_value = $this->uuid();
      $this->utoken = $cookie_value;
      $this->setHeader($this->utoken);
    }else{
      $this->utoken = $_COOKIE["utoken"];
      $this->setHeader($this->utoken);
    }
  }

	/**
	 * [setHeader description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * prepares http request for all requests
	 * @param [array] body
	 * @param [int] http_code [optional]
	 * @return void
	 */
  private function setHeader($body, $http_code = null){
    $output = json_encode($body);
		if(empty($http_code)){
			$http_code = 200;
		}
    $this->output->set_header("Access-Control-Allow-Credentials: true");
    $this->output->set_header("Access-Control-Allow-Origin: *");
    $this->output->set_status_header($http_code);
    $this->output->set_content_type('application/json');
    $this->output->set_header('utoken:'.$this->utoken);
    $this->output->set_header('gid:'.$this->gid);
    $this->output->set_output($output);
    $this->output->_display();
    exit;
  }

	/**
	 * [setRedis_create description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * create a new game in redis
	 * @param [string] gid
	 * @param [string] utoken
	 * @param [string] nick
	 * @param [array] matrix
	 * @param [array] words
	 * @param [array] info
	 * @return void
	 */
  private function setRedis_create($gid, $utoken, $nick, $matrix, $words, $info){
    $client = new Predis\Client([
      'scheme' => 'tcp',
      'host'   => '127.0.0.1',
      'port'   => 6379,
    ]);

    $body = ['gameId'=>$gid, 'players'=>[$utoken], 'matrix'=>$matrix, 'words_found'=>[], 'players_nick'=>[$utoken=>$nick], 'info'=>$info,
						 'words'=>$words, 'status'=>'waiting', 'current_player'=>$utoken, 'scores'=>[$utoken=>0], 'turn_seq'=>[$utoken], 'last_played'=>'',
					   'admin'=>$utoken];
		$body = json_encode($body);
    $client->set($gid, $body);
  }

	/**
	 * [setRedis_set description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * set the game object to redis
	 * @param [string] gid
	 * @param [array] object
	 * @return void
	 */
	private function setRedis_set($gid, $object){
    $client = new Predis\Client([
      'scheme' => 'tcp',
      'host'   => '127.0.0.1',
      'port'   => 6379,
    ]);
		$object = json_encode($object);
    $client->set($gid, $object);
  }

	/**
	 * [setRedis_info description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * get game object from redis
	 * @param [string] $gid
	 * @return [array]
	 */
	private function setRedis_info($gid){
    $client = new Predis\Client([
      'scheme' => 'tcp',
      'host'   => '127.0.0.1',
      'port'   => 6379,
    ]);
		$exists = $client->exists($gid);
		if($exists){
			return json_decode($client->get($gid), true);
		}else{
			return;
		}
  }

	/**
	 * [cheat description]
	 * written by Ankit Vaishnav on 2016-10-13
	 * generates the map of words and grid coordinated
	 * @return [array]
	 */
	public function cheat(){
		$gameId = $this->input->get('gameId');
		$game = $this->setRedis_info($gameId);
		echo "<pre>";
		print_r($game['info']);
		echo "</pre>";
	}


}
