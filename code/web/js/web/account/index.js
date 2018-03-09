;
var account_index_ops = {
    init:function(){
        this.eventBind();
    },
    eventBind:function(){
        var that = this;
        $(".search").click(function() {
            $(".wrap_search").submit();
        });

        $(".remove").click(function() {
            if(!confirm("使唔使删除?")){
                return;
            }
            that.ops("remove", $(this).attr("data"));
        });

        $(".recover").click(function() {
            if(!confirm("使唔使恢复?")){
                return;
            }
            that.ops("recover", $(this).attr("data"));
        });
    },

    ops:function(act,uid){
        $.ajax({
            url:common_ops.buildWebUrl("/account/ops"),
            type:'POST',
            data:{
                act:act,
                uid:uid
            },
            dataType:'json',
            success:function(res){
                alert(res.msg);
                if(res.code == 200){
                    window.location.href = window.location.href;
                }
            }
        });
    }
};

$(document).ready(function(){
    account_index_ops.init();
});