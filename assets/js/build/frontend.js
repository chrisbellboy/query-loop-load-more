!function(){const e={},t=new IntersectionObserver((t=>{if(t[0].intersectionRatio<=0)return;const n=t[0].target.href,r=t[0].target.closest(".wp-block-query").querySelector(".wp-block-post-template"),l=t[0].target,c=(e=>{let t=null,o=null;const n=e.match(/query-(\d+)/),r=e.match(/page=(\d+)/);return n&&(t=parseInt(n[1],10)),r&&(o=parseInt(r[1],10)),null===t||null===o?null:{query:t,page:o}})(l.getAttribute("href"));var s,u;c&&0==(s=c.query,u=c.page,!(!e[s]||!e[s].includes(u)))&&(((t,o)=>{e[t]||(e[t]=[]),e[t].push(o)})(c.query,c.page),o(n,r,l))})),o=(e,t,o)=>{n(),fetch(e,{method:"GET",headers:{"Content-Type":"text/html"}}).then((function(e){if(e.ok)return e.text();throw new Error("Network response was not ok.")})).then((function(n){const l=document.createElement("div");l.innerHTML=n;const c=l.querySelector(".wp-block-post-template");t.insertAdjacentHTML("beforeend",c.innerHTML),window.history.pushState({},"",e);const s=o.nextElementSibling;s&&s.classList.contains("wp-load-more__button__no-more-posts")&&t.classList.add("wp-block-post-template__no-more-posts"),o.remove(),r()})).catch((function(e){console.error("Fetch error:",e)}))},n=()=>{const e=document.querySelectorAll(".wp-load-more__infinite-scroll");e?.length&&e[0].classList.add("loading")},r=()=>{const e=document.querySelectorAll(".wp-load-more__infinite-scroll");if(!e?.length)return;e[0].classList.remove("loading");const o=document.querySelector(".wp-load-more__button");o&&t.observe(o)};document.addEventListener("DOMContentLoaded",(function(){"use strict";const e=document.querySelectorAll(".wp-load-more__button"),n=document.querySelectorAll(".wp-load-more__infinite-scroll");e?.length&&e.forEach((function(e){e.addEventListener("click",(function(e){e.preventDefault();const t=e.target,n=t.closest(".wp-block-query").querySelector(".wp-block-post-template"),r=t.getAttribute("href");t.innerText=t.getAttribute("data-loading-text"),o(r,n,t)}))})),n?.length&&t.observe(document.querySelector(".wp-load-more__button"))}))}();