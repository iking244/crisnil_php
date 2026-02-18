function pass_show()
    {
        var init = document.getElementById("password_visible");

        if (init.type === "password"){
            init.type = "text";
        }

        else{
            init.type = "password";
        }
    }