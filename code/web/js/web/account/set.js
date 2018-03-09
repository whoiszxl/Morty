;

var account_set_ops = {
    //初始化方法,初始化事件
    init:function(){
        this.eventBind();
    },

    //初始化事件绑定
    eventBind:function(){

        //获取到save点击按钮
        $(".wrap_account_set .save").click( function(){
            //获取到这个按钮对象
            var btn_target = $(this);
            //点击之前判断是否存在这个class
            if( btn_target.hasClass("disabled") ){
                //存在这个就提示不能重复提交了
                common_ops.alert("唔要重复提交~");
                return;
            }

            //获取姓名,手机,邮箱,登录名,密码的对象和文本值
            var nickname_target = $(".wrap_account_set input[name=nickname]");
            var nickname = nickname_target.val();
            var mobile_target = $(".wrap_account_set input[name=mobile]");
            var mobile = mobile_target.val();
            var email_target = $(".wrap_account_set input[name=email]");
            var email = email_target.val();
            var login_name_target = $(".wrap_account_set input[name=login_name]");
            var login_name = login_name_target.val();
            var login_pwd_target = $(".wrap_account_set input[name=login_pwd]");
            var login_pwd = login_pwd_target.val();

            //有效性验证
            if( nickname.length < 1 ){
                common_ops.tip( "请输入符合规范的姓名~~" ,nickname_target );
                return;
            }

            if( mobile.length < 1 ){
                common_ops.tip("请输入符合规范的手机号码~~",mobile_target);
                return;
            }

            if( email.length < 1  ){
                common_ops.tip("请输入符合规范的邮箱地址~~",email_target);
                return;
            }

            if( login_name.length < 1  ){
                common_ops.tip("请输入符合规范的登录名~~",login_name_target);
                return;
            }

            if( login_pwd.length < 1  ){
                common_ops.tip("请输入符合规范的登录密码~~",login_pwd_target);
                return;
            }

            //添加一个锁住按钮的class
            btn_target.addClass("disabled");

            //拼装数据
            var data = {
                nickname:nickname,
                mobile:mobile,
                email:email,
                login_name:login_name,
                login_pwd:login_pwd,
                id:$(".wrap_account_set input[name=id]").val()
            };

            //通过ajax发送到set接口
            $.ajax({
                url:common_ops.buildWebUrl("/account/set") ,
                type:'POST',
                data:data,
                dataType:'json',
                success:function(res){
                    btn_target.removeClass("disabled");
                    var callback = null;
                    if( res.code == 200 ){
                        callback = function(){
                            window.location.href = common_ops.buildWebUrl("/account/index");
                        }
                    }
                    common_ops.alert( res.msg,callback );
                }
            });
        });
    }
};

$(document).ready( function(){
    account_set_ops.init();
});