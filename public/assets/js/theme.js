/*
Template Name: Opixo - Tailwind CSS Multipurpose Landing Page Template
Version: 1.0
Author: coderthemes
Email: support@coderthemes.com
File: theme.js
*/

// Lucid Icons
lucide.createIcons();

// Sticky Navbar
function windowScroll() {
    const navbar = document.getElementById("navbar-sticky");
    const navbarpassticky = document.getElementById("navbarpas-sticky");
    if (navbar) {
        if (document.body.scrollTop >= 50 || document.documentElement.scrollTop >= 50) {
            navbar.classList.remove("dark:text-gray-800")
            navbar.classList.remove("text-white");
            navbar.classList.add("dark:bg-gray-800")
            navbar.classList.add("bg-white");
            navbar.classList.add("text-gray-800");
            navbar.classList.add("dark:text-white");
            navbar.classList.add("nav-sticky");

        } else {
            navbar.classList.remove("dark:bg-gray-800")
            navbar.classList.remove("bg-white");
            navbar.classList.remove("nav-sticky");
            navbar.classList.remove("text-gray-800");
            navbar.classList.remove("dark:text-white");
            navbar.classList.add("text-white");
            navbar.classList.add("dark:text-gray-800");
        }
    }
    if (navbarpassticky) {
        if (document.body.scrollTop >= 50 || document.documentElement.scrollTop >= 50) {
        } else {
        }
    }
}
window.addEventListener("scroll", (ev) => {
    ev.preventDefault();
    windowScroll();
});

// Navbar Active Class
var spy = new Gumshoe('#navbar-navlist a', {
    // Active classes
    // navClass: 'active', // applied to the nav list item
    // contentClass: 'active', // applied to the content
    offset: 80
});


// Back To Top Start   
window.onscroll = function () {
    scrollFunction();
};

function scrollFunction() {
    var mybutton = document.getElementById("back-to-top");
    if (mybutton != null) {
        if (document.body.scrollTop > 500 || document.documentElement.scrollTop > 500) {
            mybutton.classList.add("opacity-100");
            mybutton.classList.remove("opacity-0");
        } else {
            mybutton.classList.add("opacity-0");
            mybutton.classList.remove("opacity-100");
        }
    }
}

function topFunction() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}

function init() {
    (window.onload = (function () {
        function t(t, e, n) {
            (this.toRotate = e),
                (this.el = t),
                (this.loopNum = 0),
                (this.period = parseInt(n, 10) || 2e3),
                (this.txt = ""),
                this.tick(),
                (this.isDeleting = !1);
        }
        t.prototype.tick = function () {
            var t = this.loopNum % this.toRotate.length,
                t = this.toRotate[t],
                e =
                    (this.isDeleting
                        ? (this.txt = t.substring(0, this.txt.length - 1))
                        : (this.txt = t.substring(0, this.txt.length + 1)),
                        (this.el.innerHTML = '<span class="text-wrap">' + this.txt + "</span>"),
                        this),
                n = 200 - 100 * Math.random();
            this.isDeleting && (n /= 2),
                this.isDeleting || this.txt !== t
                    ? this.isDeleting &&
                    "" === this.txt &&
                    ((this.isDeleting = !1), this.loopNum++, (n = 500))
                    : ((n = this.period), (this.isDeleting = !0)),
                setTimeout(function () {
                    e.tick();
                }, n);
        };
        for (
            var e = document.getElementsByClassName("typewrite"), n = 0;
            n < e.length;
            n++
        ) {
            var i = e[n].getAttribute("data-type"),
                s = e[n].getAttribute("data-period");
            i && new t(e[n], JSON.parse(i), s);
        }
    })());
}
init();
