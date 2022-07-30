// -------------- Owl Carousel 2  --------------
$(document).ready(function() {

    $(".owl-carousel").owlCarousel({

        loop: true,
        //nav:true,
        mergeFit: true,
        dots: true,
        autoplay: 4000, //ou true
        autoplayHoverPause: true,
        lazyLoad: true,
        dotsEach: true,
        //autoplayTimeout:2000,
        //autoWidth:true,
        //navText:["Reculer","Avancer"],//


        // Le réglage du responsive
        responsive: {
            // breakpoint from 0 up
            0: {
                items: 1,
            },
            // breakpoint from 480 up
            480: {
                items: 1,
            },
            // breakpoint from 768 up
            768: {
                items: 1,
            },
            // breakpoint from 992 up
            992: {
                items: 1,
            },
            // breakpoint from 1200 up
            1200: {
                items: 1,
            }
        }
    });

});