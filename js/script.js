function launchIntoFullscreen(element) 
{
    if ( element.requestFullscreen ) 
    {
      element.requestFullscreen();
    } 
    else if ( element.mozRequestFullScreen ) 
    {
      element.mozRequestFullScreen();
    } 
    else if ( element.webkitRequestFullscreen ) 
    {
      element.webkitRequestFullscreen();
    } 
    else if ( element.msRequestFullscreen ) 
    {
      element.msRequestFullscreen();
    }
}

function isFullScreen() {
return Math.abs(screen.width - window.innerWidth) < 10; 
}

$(document).ready(function()
{
	$("#chart-area").attr('height', $(window).height() - 180).attr('width', $(window).width() - 200);
});

$(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange MSFullscreenChange', function()
{
	$("#chart-area").attr('height', $(window).height() - 180).attr('width', $(window).width() - 200);
	window.myPie.resize();

	console.log('test');
});

$(document).resize(function()
{
	$("#chart-area").attr('height', $(window).height() - 180).attr('width', $(window).width() - 200);
	window.myPie.resize();
});