/*
Template Name: Opixo - Tailwind CSS Multipurpose Landing Page Template
Version: 1.0
Author: coderthemes
Email: support@coderthemes.com
File: swiper.js
*/

function feedback() {
    var swiper = new Swiper(".feedback-swiper", {
        loop: true,
        slidesPerView: 1,
        spaceBetween: 30,
        spaceBetween: 30,
        centeredSlides: true,
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },

        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        breakpoints: {
            400: {
                slidesPerView: 1,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 40,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 50,
            },
        },
    });
}

feedback();

// #testimonial_directory Swiper  --------
function testimonial_directory() {
    new Swiper("#testimonial_directory", {
        slidesPerView: 1,
        spaceBetween: 30,
        spaceBetween: 30,
        centeredSlides: true,
        loop: true,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
    });
}
testimonial_directory();

// Auth swiper
function authSwiper() {
    swiper = new Swiper(".auth-swiper", {
        loop: !0,
        autoplay: {
            delay: 2500,
            disableOnInteraction: !1
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            clickable: !0, el: ".swiper-pagination"
        },
    });
}
authSwiper();