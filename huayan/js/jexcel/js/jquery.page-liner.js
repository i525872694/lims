(function($) { "use strict";
    var pageLiner = {
        isShow: false,
        init: function(labelCorner){
            return false;
            this.isShow = true;
            $('body').append('<div class="pglnr-ext-ruler pglnr-ext-ruler-top"><ul style="width: 100%;"></ul></div>');
            $('body').append('<div class="pglnr-ext-ruler pglnr-ext-ruler-left"><ul style="height: 100%;"></ul></div>');

            // 纵版
            $('body').append('<div class="pglnr-ext-helpline pglnr-ext-helpline-x"><div class="pglnr-ext-helpline-tooltip pglnr-ext-helpline-tooltip-x">#1: 760px</div></div>');
            $('.pglnr-ext-helpline-x:eq(0)').css({
                'background-color': 'rgb(51, 255, 255)',
                'position': 'fixed',
                'left': '760px',
                'box-shadow': 'rgba(51, 255, 255, 0.7) 0px 0px 5px 0px'
            });
            $('body').append('<div class="pglnr-ext-helpline pglnr-ext-helpline-y" style=""><div class="pglnr-ext-helpline-tooltip pglnr-ext-helpline-tooltip-y">#2: 1088px</div></div>');
            $('.pglnr-ext-helpline-y:eq(0)').css({
                'background-color': 'rgb(51, 255, 255)',
                'position': 'absolute',
                'top': '1088px',
                'box-shadow': 'rgba(51, 255, 255, 0.7) 0px 0px 5px 0px'
            });

            // 横版
            $('body').append('<div class="pglnr-ext-helpline pglnr-ext-helpline-x"><div class="pglnr-ext-helpline-tooltip pglnr-ext-helpline-tooltip-x">#1: 1088px</div></div>');
            $('.pglnr-ext-helpline-x:eq(1)').css({
                'background-color': 'rgb(51, 255, 255)',
                'position': 'fixed',
                'left': '1088px',
                'box-shadow': 'rgba(51, 255, 255, 0.7) 0px 0px 5px 0px'
            });
            $('body').append('<div class="pglnr-ext-helpline pglnr-ext-helpline-y" style=""><div class="pglnr-ext-helpline-tooltip pglnr-ext-helpline-tooltip-y">#2: 760px</div></div>');
            $('.pglnr-ext-helpline-y:eq(1)').css({
                'background-color': 'rgb(51, 255, 255)',
                'position': 'absolute',
                'top': '760px',
                'box-shadow': 'rgba(51, 255, 255, 0.7) 0px 0px 5px 0px'
            });
            // 调整标尺原点位置
            typeof labelCorner == 'object' && this.resize(labelCorner);
        },
        resize: function(top, left){
            if(!this.isShow){
                return false;
            }
            top || (top = 0);
            left || (left = 0);
            if(typeof top == 'object'){
                var label_corner = top;
                top = $(label_corner).offset().top + $(label_corner).outerHeight();
                left = $(label_corner).offset().left + $(label_corner).outerWidth();
            }
            $('.pglnr-ext-ruler-top').css('top', top -18 + 'px');
            $('.pglnr-ext-ruler-left ul').css('top', top -18 + 'px');

            var A4_config = [760, 1088];
            $('.pglnr-ext-helpline-x').css('top', top + 'px');
            $('.pglnr-ext-helpline-x').each(function(i){
                $(this).css({
                    left: A4_config[i] + parseInt(left) + 'px'
                })
            });

            $('.pglnr-ext-helpline-y').css('left', left + 'px');
            $('.pglnr-ext-helpline-y').each(function(i){
                $(this).css({
                    top: A4_config[i] + parseInt(top) + 'px'
                })
            });


            $('.pglnr-ext-ruler-top ul').css('left', left + 'px');
            $('.pglnr-ext-ruler-left').css('left', left - 18 + 'px');

            var n = Math.ceil($('.pglnr-ext-ruler.pglnr-ext-ruler-top ul').width()/100);
            $('.pglnr-ext-ruler-top ul').empty();
            for (let i = 0; i < n; i++) {
                $('.pglnr-ext-ruler-top ul').append('<li></li>');
            }
        }
    }
    $.fn.pageLiner = pageLiner;
})(jQuery);