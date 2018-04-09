// document.getElementById('notification').onclick = () => {
// 	document.getElementById('notification-container').classList.toggle('hidden');
// }
$(document).ready(function(){
// 	setInterval(function(){
// 		$.pjax.reload('#pjax-container')
// 	}, 100000);
        let urlSite = window.location.origin+'/frontend/web';
        changeStatus('.trNew', urlSite+'/zakaz/adopted?id=');
        changeStatus('.trNewMaster', urlSite+'/zakaz/adopmaster?id=');
        changeStatus('.trNewDisain', urlSite+'/zakaz/adopdisain?id=');
        addClassForm('.startShift', '#form-startShift', '.form-shiftStart');
        addClassForm('.endShift', '#form-endShift', '.form-shiftEnd');

        $( 'body' ).on( 'click', '.commentButton', function() {
            $( ".CommentForm" ).toggleClass( "CommentForm-visible" );
        });
       $('body').on('change', '#zakaz-status', function () {
                $('#autsors')
                    .css({'display': ($(this).val() == 8 ? 'block' : 'none')})
                    .prop('selectedIndex', 0)
       });
       $('body').on('click', '#checkboxAppoint', function () {
           $('.form-appoint').toggleClass('visible');
       });
       $('#zakaz-status').each(function () {
            if ($(this).val() == 8){
                $('#autsors').css('display', 'block')
            } else {
                $('#autsors').css('display', 'none')
                    .prop('selectedIndex', 0);
            }
       });

       /** clicked on delivared from the buyer */
       $('.sendGood').click(function (e) {
           e.preventDefault();
           let url = $(this).attr('href');
           let id = $(this).attr('id');
           $.ajax({
               type: 'get',
               url: urlSite+'/'+url
           }).done(result => {
               if(result === '1'){
                $('#'+id).parents('tr').remove();
               } else {
                   console.log(result);
               }
           })
       });

    function addClassForm(button, form, formSecond){
        $(button).click(function () {
            $(form)[0].reset();
            $(formSecond).toggleClass('visibleForm');
        });
    }
    function changeStatus(tr, url) {
        $('body').on("click", tr, function () {
            let data = $(this).data("key");
            $.ajax({
                url: url+data,
            })
                .done(() => console.log('Статус успешно изменен'))
                .fail(err => console.error(err))
        });
    }
    $(function () {
        $("[data-toggle = 'tooltip']").tooltip();
    });
    $(function () {
        $("[data-toggle='popover']").popover();
    });
});