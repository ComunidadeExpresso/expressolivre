(function()
{

  var formLogin = null;

  function msgLogin()
  {
      var divMsgLogin = $("#msg-login");

      var msgType = divMsgLogin.html();

      if( $.trim(msgType) != "" )
      {  
        divMsgLogin.css("display","block");

        if( msgType.indexOf("color=\"FF0000\"") > -1 )
        {
          divMsgLogin.attr("class","erro").delay(3000).fadeOut('slow');
        }
        else
        {
          divMsgLogin.attr("class","sucesso").delay(3000).fadeOut('slow');
        }
      }
  }

  function setLogin()
  {

    if( formLogin.find("select[name=organization]").length > 0 )
    {
      formLogin.find("input[name=login]").val( formLogin.find("select[name=organization]").val() + "-"  + formLogin.find("input[name=user]").val() );
    }
    else
    {
      formLogin.find("input[name=login]").val( formLogin.find("input[name=login]").val() );
    }
  }

  function loginExpresso()
  {
    $(document).ready(function()
    {   
      // Element Form
      formLogin = $("form[name=flogin]");

      // Msg Expresso  
      msgLogin();

      // KeyBoard Virtual   
      var keyBoardV = formLogin.find("input[name=show_kbd]").val();
    
      if( $.trim(keyBoardV) != "" && keyBoardV == "True" )
      {
        // Keyboard virtual
        $('#passwd').keypad({
             keypadOnly: false,
             showOn: 'button', 
             layout: $.keypad.qwertyLayout,
             buttonImageOnly: true,
             buttonImage: './prototype/plugins/jquery.keyboard/keypad.png'
        });
      }

      // Captcha      
      var captcha = $("#captcha");
          captcha.css("position","absolute");
          captcha.css("left","115px");

      if( $.trim(keyBoardV) != "" && keyBoardV == "True" )
      {  
        captcha.css("top","272px");

        if( $("#organizacao").parent().css('display') == "block" )
        {
          captcha.css("top","327px");
        }
      }
      else
      {
        captcha.css("top","263px");

        if( $("#organizacao").parent().css('display') == "block" )
        {
          captcha.css("top","318px");
        }
      }

    });
  }

  loginExpresso.prototype.setLogin      = setLogin;

  window.loginExpresso = new loginExpresso;

})();
