<?php

class algo {

  public function assign_rand_value($num) {
    // accepts 1 - 36
    switch($num) {
        case "1"  : $rand_value = "a"; break;
        case "2"  : $rand_value = "b"; break;
        case "3"  : $rand_value = "c"; break;
        case "4"  : $rand_value = "d"; break;
        case "5"  : $rand_value = "e"; break;
        case "6"  : $rand_value = "f"; break;
        case "7"  : $rand_value = "g"; break;
        case "8"  : $rand_value = "h"; break;
        case "9"  : $rand_value = "i"; break;
        case "10" : $rand_value = "j"; break;
        case "11" : $rand_value = "k"; break;
        case "12" : $rand_value = "l"; break;
        case "13" : $rand_value = "m"; break;
        case "14" : $rand_value = "n"; break;
        case "15" : $rand_value = "o"; break;
        case "16" : $rand_value = "p"; break;
        case "17" : $rand_value = "q"; break;
        case "18" : $rand_value = "r"; break;
        case "19" : $rand_value = "s"; break;
        case "20" : $rand_value = "t"; break;
        case "21" : $rand_value = "u"; break;
        case "22" : $rand_value = "v"; break;
        case "23" : $rand_value = "w"; break;
        case "24" : $rand_value = "x"; break;
        case "25" : $rand_value = "y"; break;
        case "26" : $rand_value = "z"; break;
    }
    return $rand_value;
  }

  public function wordEngine(){
    $getSomeWords = $this->getSomeWords();
    if(!$getSomeWords['status']){
      return $getSomeWords;
    }
    $words = $getSomeWords['body'];
    $result = $this->matrixMaker($words);
    $matrix = $result['matrix'];
    $info = $result['info'];
    return ['status'=>1, 'words'=>$words, 'matrix'=>$matrix, 'info'=>$info];
  }

  public function matrixMaker($words){
    $matrix = [];
    $info = [];
    for($i=0;$i<count($words);$i++){
      $ok = 0;
      while($ok<1){
        $maker = $this->hMaker($matrix, $words[$i]);
        $ok = $maker['status'];
      }
      $matrix = $maker['matrix'];
      $info[$words[$i]] = ['start'=>$maker['start'], 'type'=>$maker['type']];
    }
    for($i=0;$i<15;$i++){
      for($j=0;$j<15;$j++){
        if(empty($matrix[$i][$j])){
          $matrix[$i][$j] = $this->assign_rand_value(rand(1,26));
        }
      }
    }
    return ['matrix'=>$matrix, 'info'=>$info];
  }

  public function hMaker($matrix, $word){
    $characters = str_split($word);
    $length = strlen($word);
    $ok = 0;
    $par = ['h', 'v', 'd'];
    $orient = $par[rand(0, 2)];

    if($orient == 'v'){
      $h_rand = rand(0,14);
      $v_rand = rand(0,14 - $length);
      for($i=0;$i<$length;$i++){
        if(!empty($matrix[$v_rand+$i][$h_rand])){
          if($matrix[$v_rand+$i][$h_rand]!=$characters[$i]){
            $ok=0;
            break;
          }else{
            $ok = 1;
          }
        }else{
          $ok = 1;
        }
      }
      if($ok==1){
        for($i=0;$i<$length;$i++){
          $matrix[$v_rand+$i][$h_rand] = $characters[$i];
        }
        return ['status'=>1, 'matrix'=>$matrix, 'start'=>($v_rand+1)."-".($h_rand+1), 'type'=>$orient];
      }else{
        return ['status'=>0, 'matrix'=>'', 'start'=>'', 'type'=>''];
      }
    }

    if($orient == 'h'){
      $h_rand = rand(0,14 - $length);
      $v_rand = rand(0,14);
      for($i=0;$i<$length;$i++){
        if(!empty($matrix[$v_rand][$h_rand+$i])){
          if($matrix[$v_rand][$h_rand+$i]!=$characters[$i]){
            $ok=0;
            break;
          }else{
            $ok = 1;
          }
        }else{
          $ok = 1;
        }
      }
      if($ok==1){
        for($i=0;$i<$length;$i++){
          $matrix[$v_rand][$h_rand+$i] = $characters[$i];
        }
        return ['status'=>1, 'matrix'=>$matrix, 'start'=>($v_rand+1)."-".($h_rand+1), 'type'=>$orient];
      }else{
        return ['status'=>0, 'matrix'=>'', 'start'=>'', 'type'=>''];
      }
    }

    if($orient == 'd'){
      $h_rand = rand(0,14 - $length);
      $v_rand = rand(0,14 - $length);
      for($i=0;$i<$length;$i++){
        if(!empty($matrix[$v_rand+$i][$h_rand+$i])){
          if($matrix[$v_rand+$i][$h_rand+$i]!=$characters[$i]){
            $ok=0;
            break;
          }else{
            $ok = 1;
          }
        }else{
          $ok = 1;
        }
      }
      if($ok==1){
        for($i=0;$i<$length;$i++){
          $matrix[$v_rand+$i][$h_rand+$i] = $characters[$i];
        }
        return ['status'=>1, 'matrix'=>$matrix, 'start'=>($v_rand+1)."-".($h_rand+1), 'type'=>$orient];
      }else{
        return ['status'=>0, 'matrix'=>'', 'start'=>'', 'type'=>''];
      }
    }

  }

  public function getSomeWords(){
    $finalWords = [];
    $full = $this->getAllWords();
    if($full['status']){
      $words = $full['body'];
      if(!empty($words) && count($words) > 0){
        $count = count($words);
        for($i=0;$i<11;$i++){
          $newWord = $words[rand(0,$count-1)];
          if(in_array($newWord, $finalWords)){
            $i = $i - 1;
          }else{
            $finalWords[] = $newWord;
          }
        }
        return ['status'=>1, 'message'=>'Success', 'body'=>$finalWords];
      }else{
        return ['status'=>0, 'message'=>'Error', 'body'=>'Error in getSomeWords!'];
      }
    }else{
      return $full;
    }
  }

  public function getAllWords(){
    $handle = fopen("assets/en-US.dic", "r");
    $words = [];
    $i=0;
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
          $line = trim(preg_replace('/\s\s+/', ' ', $line));
          $line = strtolower($line);
          $yy = strpos($line, "'");
          if(!is_numeric($yy) && strlen($line) < 15 && strlen($line) > 2){
            $words[] = $line;
          }
        }
        fclose($handle);
        return ['status'=>1, 'message'=>'Success', 'body'=>$words];
    } else {
        return ['status'=>0, 'message'=>'Error', 'body'=>'Unable to read dictionary file!'];
    }
  }

  public function checkWord($game, $word, $utoken){
		$word = strtolower($word);
		if(in_array($word, $game['words_found'])){
			return ['status'=>0, 'msg'=>'Word already found!', 'game'=>$game];
		}
		if(in_array($word, $game['words'])){
			array_push($game['words_found'], $word);
      $game['scores'][$utoken] = $game['scores'][$utoken] + 1;
			return ['status'=>1, 'msg'=>'', 'game'=>$game];
		}
    $getSomeWords = $this->getSomeWords();
    if(!$getSomeWords['status']){
      return ['status'=>0, 'msg'=>'Dictionary mismatch error!', 'game'=>$game];
    }
    $words = $getSomeWords['body'];
    if(in_array($word, $words)){
      array_push($game['words_found'], $word);
      $game['scores'][$utoken] = $game['scores'][$utoken] + 1;
      return ['status'=>1, 'msg'=>'', 'game'=>$game];
    }
		return ['status'=>0, 'msg'=>'No match for this word...Try again!', 'game'=>$game];
	}

}
