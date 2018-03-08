
var user_edit_ops = {

    init:function(){
        this.eventBind();
    },
    eventBind:function(){
        $(".save").click( function() {

            var btn_target = $(this);
            if(btn_target.hasClass("disabled")){
                alert("不肯你重复点击哦");
                return false;
            }

            var nickname = $(".user_edit_wrap input[name=nickname]").val();
            var email = $(".user_edit_wrap input[name=email]").val();

            if(nickname.length < 2){
                alert("请输入合法的姓名");
                return false;
            }

            if(email.length < 5){
                alert("请输入合法的邮箱");
                return false;
            }

            btn_target.addClass("disabled");

            $.ajax({
                url:'/web/user/edit',
                type:'POST',
                data:{
                    nickname:nickname,
                    email:email
                },
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

$(document).ready( function(){
    user_edit_ops.init();
});