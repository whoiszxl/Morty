;
var mod_pwd_ops = {
    init:function(){
        this.eventBind();
    },
    eventBind:function(){
        var that = this;
        $("#save").click(function(){
            var btn_target  = $(this);
            if( btn_target.hasClass("disabled") ){
                alert("正在处理，请不要频繁点击~~");
                return;
            }

            var old_password = $("#old_password").val();
            if(!old_password){
                alert("请输入原密码~~");
                return false;
            }

            var new_password = $("#new_password").val();
            if(!new_password || new_password.length<6){
                alert("请输入不少于6位的新密码!");
                return false;
            }

            btn_target.addClass("disabled");

            var data = {
                'old_password':old_password,
                'new_password':new_password
            };
            $.ajax({
                url:'/web/user/reset-pwd',
                type:'POST',
                data:data,
                dataType:'json',
                success:function(res){
                    btn_target.removeClass("disabled");
                    alert(res.msg);
                    if(res.code == 200){
                        window.location.href = window.location.href;
                    }
                }
            });
        });

    }
};
$(document).ready(function(){
    mod_pwd_ops.init();
});