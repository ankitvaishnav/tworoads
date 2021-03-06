var common = {

  utoken: '',

  gameId: '',

  isAdmin: 0,

  url: 'http://loc.netsberg.com/index.php/',

  /**
   * [saveGameId description]
   * written by Ankit Vaishnav on 2016-10-12
   * core function that save gameId into the localStorage
   * @param  {[string]} gameId [description]
   */
  saveGameId:function(gameId){
    if (typeof(Storage) !== "undefined") {
      common.gameId = gameId;
      localStorage.gameId = gameId;
    } else {
      common.showAlerts('danger', 'No web storage support!');
    }
  },

  /**
   * [getLogin description]
   * written by Ankit Vaishnav on 2016-10-12
   * check the user already has unique identifier (player_id), if not the get one from server and save in localStorage
   */
  getLogin:function(){
    if (typeof(Storage) !== "undefined") {
      if(localStorage.utoken && localStorage.utoken !== 'undefined' && localStorage.utoken !== 'null'){
        common.utoken = localStorage.utoken;
        if(localStorage.gameId && typeof(localStorage.gameId) !== "undefined"){
          common.gameId = localStorage.gameId;
          common.info();
        }
      }else{
        $.post(common.url+'auth',{},function(data,status,xhr){
          common.utoken = data;
          localStorage.utoken = common.utoken;
        });
      }
      // $('#yourId').html(common.utoken);
    } else {console.log('od');
        common.showAlerts('danger', 'No web storage support!');
    }

  },

  /**
   * [join description]
   * written by Ankit Vaishnav on 2016-10-12
   * send ajax post request to join the game
   */
  join:function(){
    var gameId = $('#gameId').val();
    var nick = $('#j_nick').val();
    if( gameId && gameId.length > 0 ){
      var object = {'gameId':gameId, 'nick':nick, 'utoken':common.utoken};
      $.ajax({
        type: "POST",
        url: common.url+'join',
        data: object,
        success: function(data,status,xhr){
          if(data && xhr.status==200){
            if(data.msg){
              common.showAlerts('success', data.msg);
            }
            common.gameId = gameId;
            common.info();
          }else{
            common.showAlerts('warning', data.msg);
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           common.showAlerts('danger', XMLHttpRequest.responseText);
        }
      });
    }else{
      common.showAlerts('danger', 'Please enter game Id!');
    }
  },

  /**
   * [create description]
   * written by Ankit Vaishnav on 2016-10-12
   * sends ajax post request to create a game
   */
  create:function(){
    var nick = $('#nick').val();
    var object = {'utoken':common.utoken, 'nick':nick};
    $.ajax({
      type: "POST",
      url: common.url+'create',
      data: object,
      success: function(data,status,xhr){
        if(data && xhr.status==200){
          common.saveGameId(data.gameId);
          common.info();
        }else{
          common.showAlerts('warning', data.msg);
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         common.showAlerts('danger', XMLHttpRequest.responseText);
      }
    });
  },

  /**
   * [info description]
   * written by Ankit Vaishnav on 2016-10-12
   * sends ajax post request to get the information related to the game
   */
  info:function(){
    var object = {'utoken':common.utoken, 'gameId':common.gameId};
    $.ajax({
      type: "POST",
      url: common.url+'info',
      data: object,
      success: function(data,status,xhr){
        if(data && xhr.status==200){
          common.gridMaker(data);
          common.infoEditor(data);
        }else{
          common.showAlerts('warning', data);
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         common.showAlerts('danger', XMLHttpRequest.responseText);
      }
    });
  },

  /**
   * [start description]
   * written by Ankit Vaishnav on 2016-10-12
   * send ajax post request to start a game
   */
  start:function(){
    var object = {'utoken':common.utoken, 'gameId':common.gameId};
    $.ajax({
      type: "POST",
      url: common.url+'start',
      data: object,
      success: function(data,status,xhr){
        if(data && xhr.status==200){
          common.showAlerts('success', data.msg);
          common.info();
        }else{
          common.showAlerts('warning', data.msg);
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         common.showAlerts('danger', XMLHttpRequest.responseText);
      }
    });
  },

  /**
   * [pass description]
   * written by Ankit Vaishnav on 2016-10-12
   * function to serve pass button event
   */
  pass:function(){
    common.submitWord(1);
  },

  /**
   * [submitWord description]
   * written by Ankit Vaishnav on 2016-10-12
   * sends ajax post request to check the word entered by player
   * @param  {[boolean]} par [to differentiate pass chance]
   */
  submitWord:function(par){
    var word = $('#word').val();
    if(par==1){
      word = '#';
    }else{
      if(!word || word.length < 1){
        common.showAlerts('warning', "please enter a word!");
        return;
      }
    }
    var object = {'utoken':common.utoken, 'gameId':common.gameId, 'word':word};
    $.ajax({
      type: "POST",
      url: common.url+'play',
      data: object,
      success: function(data,status,xhr){
        if(data && xhr.status==200){
          common.showAlerts('success', data.msg);
          common.info();
        }else{
          common.showAlerts('warning', data.msg);
          common.info();
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        common.showAlerts('danger', XMLHttpRequest.responseText);
      }
    });
  },

  /**
   * [gridMaker description]
   * written by Ankit Vaishnav on 2016-10-12
   * makes a grid in UI on the basis of alphabets grid coordinates. Additional it add control buttons like refresh, start game etc
   * @param  {[object]} data [description]
   */
  gridMaker:function(data){
    var matrix = data.matrix;
    var innerHTML = '<tbody style="box-shadow: 0px 0px 5px #888888;">';
    for(var i=0; i<15; i++){
      innerHTML += '<tr>';
      for(var j=0; j<15; j++){
        innerHTML += "<td title="+(i+1)+"-"+(j+1)+" style='cursor:pointer;'>"+matrix[i][j]+"</td>";
      }
      innerHTML += '</tr>';
    }
    innerHTML += '</tbody>';
    $('#matrixMaker').html(innerHTML);

    innerHTML = ' <div class="col-md-12" style="box-shadow: 0px 0px 5px #888888;padding:10px;">\
                    <h5><i>Your game id is '+common.gameId+'</i></h5>\
                    <h5><i>Your player id is '+common.utoken+'</i></h5>\
                    <div class="form-group hidden for-admin">\
                      <button class="form-control btn btn-primary" onclick="window.common.start()">Start game</button>\
                    </div>\
                    <div class="form-group">\
                      <input type="text" class="form-control" id="word" placeholder="Enter the word">\
                    </div>\
                    <div class="form-group">\
                      <button class="btn btn-success" onclick="window.common.submitWord()">Submit</button>\
                    </div>\
                    <div class="form-group">\
                      <button class="btn btn-warning" onclick="window.common.pass()">Pass</button>\
                    </div>\
                    <div class="form-group">\
                      <button class="btn btn-info" onclick="window.common.info()">Refresh </button>\
                    </div>\
                    <div class="col-md-12" id="info-bar">\
                    </div>\
                  </div>';
    $('#controls').html(innerHTML);
    if(common.utoken == data.admin && data.game_status == 'waiting'){
      $('.for-admin').removeClass('hidden');
    }else{
      $('.for-admin').addClass('hidden');
    }
  },

  /**
   * [infoEditor description]
   * written by Ankit Vaishnav on 2016-10-12
   * updates the game related information on the click on refesh button
   * @param  {[type]} data [description]
   */
  infoEditor:function(data){
    var innerHTML = '';
    if(data.game_status){
      innerHTML += '<h5><b>Status:</b> '+data.game_status+'</h5>';
    }
    if(data.current_player){
      innerHTML += '<h5><b>Current chance:</b> '+data.players[data.current_player]+' ('+data.current_player+')</h5>';
    }
    if(data.players){
      innerHTML += '<h5><b>Players - Scores:</b></h5><ul>';
      $.each(data.players, function( index, value ) {
        innerHTML += '<li>'+value+' ('+index+') - '+data.scores[index]+'</li>';
      });
      innerHTML += '</ul>';
    }
    if(data.words_done){
      innerHTML += '<h5><b>Words found</b></h5><ul>';
      $.each(data.words_done, function( index, value ) {
        innerHTML += '<li>'+value+'</li>';
      });
      innerHTML += '</ul>';
    }
    $('#info-bar').html(innerHTML);
  },

  /**
   * [showAlerts description]
   * written by Ankit Vaishnav on 2016-10-12
   * show alerts for specific time in the UI
   * @param  {[string]} type [description]
   * @param  {[string]} body [description]
   */
  showAlerts:function(type, body){
    if(type=='success'){
      $('.super-success h4').html(body);
      $('.super-success').removeClass('hidden');
      setTimeout(function(){ $('.super-success').addClass('hidden'); }, 3000);
    }
    if(type=='warning'){
      $('.super-warning h4').html(body);
      $('.super-warning').removeClass('hidden');
      setTimeout(function(){ $('.super-warning').addClass('hidden'); }, 3000);
    }
    if(type=='info'){
      $('.super-info h4').html(body);
      $('.super-info').removeClass('hidden');
      setTimeout(function(){ $('.super-info').addClass('hidden'); }, 3000);
    }
    if(type=='danger'){
      $('.super-danger h4').html(body);
      $('.super-danger').removeClass('hidden');
      setTimeout(function(){ $('.super-danger').addClass('hidden'); }, 3000);
    }
  }

};

$(function() {
    common.getLogin();
});
