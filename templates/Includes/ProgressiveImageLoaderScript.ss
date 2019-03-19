<%-- place this in the <head> of your document --%>
<script>
document.addEventListener('DOMContentLoaded',function(event){var targets=document.querySelectorAll('img.pil-small');if(targets.length==0){return}var image_replace=function(t){if(t.classList.contains('loaded')){return}var e=t.parentNode;t.classList.add('loaded');var n=new Image;n.src=t.dataset?t.dataset.final:t.getAttribute("data-final");n.onload=function(){n.classList.add('loaded')};e.appendChild(n);return true};var callback=function(entries,observer){entries.forEach(function(entry){if(!entry.isIntersecting){return}var t=entry.target;if(image_replace(t)){try{observer.unobserve(t)}catch(e){}}})};var supports=typeof IntersectionObserver!='undefined';var observer;if(supports){var options={root:null,rootMargin:'0px',threshold:0.5};observer=new IntersectionObserver(callback,options)}targets.forEach(function(c,i,a){supports?observer.observe(c):image_replace(c)})});
</script>
<% include ProgressiveImageLoaderStyle %>
