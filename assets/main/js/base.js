require('../css/config/base.scss');

import { Collapse, Tooltip, Popover, Modal, Carousel, Alert  } from 'bootstrap';
import '../stimulus';
import 'lightgallery/css/lightgallery.css';

// AOS.init();

let lastKnownScrollPosition = 0;
let ticking = false;
const header = document.querySelector('header#header');
function doSomething(scrollPos) {
    if (scrollPos > 15){
        header.classList.add('sticky');
    }else{
        header.classList.remove('sticky');

    }
}

document.addEventListener("scroll", (event) => {
    lastKnownScrollPosition = window.scrollY;

    if (!ticking) {
        window.requestAnimationFrame(() => {
            doSomething(lastKnownScrollPosition);
            ticking = false;
        });

        ticking = true;
    }
});


let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new Tooltip(tooltipTriggerEl)
})


document.addEventListener('DOMContentLoaded', function() {
    //<editor-fold desc="FadeOut auto / manual of Flashes">
    function removeFadeOut(el, speed) {
        const seconds = speed / 1000;
        el.style.transition = `opacity ${seconds}s ease`;

        el.style.opacity = 0;
        setTimeout(() => {
            el.parentNode.removeChild(el);
        }, speed);
    }
    const flashes = document.querySelectorAll('.flash-success, .flash-error');
    flashes.forEach((el) => {
        el.querySelector('.close').addEventListener('click', () => {
            removeFadeOut(el, 600);
        });
        setTimeout(() => {
            removeFadeOut(el, 600);
        }, 8000);
    });
    //</editor-fold>
    //<editor-fold desc="Prevent Double Submits">
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (form.classList.contains('is-submitting')) {
                e.preventDefault();
            }
            form.classList.add('is-submitting');
        });
    });
    //</editor-fold>
});