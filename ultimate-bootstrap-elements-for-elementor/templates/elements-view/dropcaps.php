<#
var ubeEscHtml = function( str ) {
	return ( str === undefined || str === null ? "" : String( str ) )
		.replace( /&(?!(?:#[0-9]+|#x[0-9a-fA-F]+|[a-zA-Z][a-zA-Z0-9]*);)/g, "&amp;" )
		.replace( /</g, "&lt;" ).replace( />/g, "&gt;" )
		.replace( /"/g, "&quot;" ).replace( /'/g, "&#039;" );
};

dropcaps_sharp ='';

if ( settings.sharp !=='' ) {
dropcaps_sharp = 'sharp-' + settings.sharp;
}

view.addRenderAttribute( 'dropcaps_warpper', 'class',[
'ube-dropcaps',
'view-'+ settings.view,
dropcaps_sharp
] );

#>
<div {{{ view.getRenderAttributeString( 'dropcaps_warpper' ) }}}>
<# if(  settings.dropcaps_text !=='' ){#>
<p>{{{ ubeEscHtml( settings.dropcaps_text ) }}}</p>
<# }#>
</div>