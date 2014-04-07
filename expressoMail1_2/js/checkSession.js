window.setInterval(
function()
{
    $.ajax(
        {
            url: "checkSession.php",
            type: "GET",
            success: function(data)
            {
                data = JSON.parse(data);

                if(data.status !== true)
                    window.location = "../../login.php";
            }
        });
},60000);