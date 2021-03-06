<script>
    var resizefunc = [];
</script>
<script src="dist/js/script.min.js"></script>

<noscript id="deferred-styles">
    <link href="https://fonts.googleapis.com/css?family=Source+Code+Pro|Source+Sans+Pro:400,600,700,300|Roboto:400,500,700,900" rel="stylesheet">
</noscript>

<script>
    var loadDeferredStyles = function() {
        var addStylesNode = document.getElementById("deferred-styles");
        var replacement = document.createElement("div");
        replacement.innerHTML = addStylesNode.textContent;
        document.body.appendChild(replacement)
        addStylesNode.parentElement.removeChild(addStylesNode);
    };
    var raf = requestAnimationFrame || mozRequestAnimationFrame ||
    webkitRequestAnimationFrame || msRequestAnimationFrame;
    if (raf) raf(function() { window.setTimeout(loadDeferredStyles, 0); });
    else window.addEventListener('load', loadDeferredStyles);
</script>