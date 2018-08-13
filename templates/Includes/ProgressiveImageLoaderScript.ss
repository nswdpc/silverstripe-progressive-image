<%-- place this in the <head> of your document --%>
<script>
	<% include ProgressiveImageWaypoints %>
	var pil_offset = '90%';
	var pil_process=function(t){var e=t.parentNode;t.classList.add("loaded");new Waypoint({context:document.getElementById("waypoint-context"),element:t,offset:pil_offset,handler:function(a){var n=new Image;n.src=t.dataset?t.dataset.final:t.getAttribute("data-final"),n.onload=function(){n.classList.add("loaded")},e.appendChild(n),this.destroy()}})};
</script>

<% include ProgressiveImageLoaderStyle %>
