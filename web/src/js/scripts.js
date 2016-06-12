// URL PARAMETER GET FUNCTION
var getUrlParameter = function getUrlParameter(sParam) {
  var sPageURL = decodeURIComponent(window.location.search.substring(1)),
    sURLVariables = sPageURL.split('&'),
    sParameterName,
    i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split('=');

    if (sParameterName[0] === sParam) {
      return sParameterName[1] === undefined ? true : sParameterName[1];
    }
  }
};

if(getUrlParameter("start")){
  // Get data
  var remaining = 10;
  function get_songs(){
    $.ajax({
      url: "api/get_songs",
      success: function(result) {
        var page = document.querySelector('#blind-test');
        var answers = page.querySelector('#answers');
        var buttons = answers.querySelectorAll('button');
        var audio = page.querySelector('audio');
        var remaining_div = page.querySelector('.remaining_div');

        var songs = JSON.parse(result);

        for (var i = 0; i < 4; i++) {
          buttons[i].textContent = songs[i].title;
        }

        source.setAttribute("src", songs.preview_url);
        audio.load();
        audio.play();

        remaining_div.textContent = remaining;
        remaining = remaining - 1;
      }
    });
  }

  get_songs();

  $("button").click(function() {
    var answer = parseInt($(this).attr('id'));
    $.ajax({
      type: "POST",
      url: 'api/user_answer',
      data: { user_answer: answer },
      success: function(result){
        if(remaining === 0){
          document.location.href="?end";
        }else{
          get_songs();
        }
      }
    });
  });
}