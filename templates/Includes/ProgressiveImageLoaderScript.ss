<%-- place this in the <head> of your document --%>
<script>
document.addEventListener('DOMContentLoaded',function(event){var callback=function(entries,observer){entries.forEach(function(entry){if(!entry.isIntersecting){return}var t=entry.target;if(t.classList.contains('loaded')){return}var e=t.parentNode;t.classList.add("loaded");var n=new Image;n.src=t.dataset?t.dataset.final:t.getAttribute("data-final");n.onload=function(){n.classList.add("loaded")};e.appendChild(n)})};var options={root:null,rootMargin:'0px',threshold:1.0};var observer=new IntersectionObserver(callback,options);var targets=document.querySelectorAll('img.pil-small');targets.forEach(function(c,i,a){observer.observe(c)})});
</script>
<% include ProgressiveImageLoaderStyle %>
