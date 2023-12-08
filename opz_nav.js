$(function () {
   $('#get-link-info-btn').click(function (e) {
       e.preventDefault();
       if ($(this).hasClass('disabled')) {
           return false;
       }
       var url = $('#opz_url').val();
       if(url.slice(0, 4) !== 'http') {
           cocoMessage.error('必须是http开头')
           return false
       }
       var oldText = $(this).text()
       $(this).text('正在获取..').removeClass('disabled').addClass('disabled')

       var that = $(this)
       $.ajax({
           type: 'post',
           url: '/admin/plugin.php?plugin=opz_nav&api=get_link_info',
           data: {
               link: url
           },
           dataType: 'json',
           success: function (resp) {
               console.log(resp)
               if (resp.code === 200) {
                   $('#title').val(resp.data.title)
                   $('#cover').val(resp.data.cover).blur()
               } else {
                   cocoMessage.error(resp.data)
               }
               that.text(oldText).removeClass('disabled')
           },
           error: function (err) {
               cocoMessage.error('请求失败')
               that.text(oldText).removeClass('disabled')
           }
       })
   })
});