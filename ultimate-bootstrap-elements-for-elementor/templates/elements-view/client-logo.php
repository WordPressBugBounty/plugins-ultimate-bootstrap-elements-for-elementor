<#
var ubeEscHtml = function( str ) {
	return ( str === undefined || str === null ? "" : String( str ) )
		.replace( /&(?!(?:#[0-9]+|#x[0-9a-fA-F]+|[a-zA-Z][a-zA-Z0-9]*);)/g, "&amp;" )
		.replace( /</g, "&lt;" ).replace( />/g, "&gt;" )
		.replace( /"/g, "&quot;" ).replace( /'/g, "&#039;" );
};
var ubeSafeUrl = function( url ) {
	url = ( url || "" ) + "";
	var scheme = _.filter( url.split( "" ), function( c ) { return c.charCodeAt( 0 ) > 32; } ).join( "" ).match( /^([a-z][a-z0-9+.-]*):/i );
	return ( ! scheme || /^(https?|ftps?|mailto|tel|sms|fax)$/i.test( scheme[1] ) ) ? url : "";
};
var client_logo_classes = [
	'ube-client-logo',
];
view.addRenderAttribute( 'client_logo_classes', 'class', client_logo_classes );

var item_class = [
	'ube-item-client-logo',
	'd-flex'
];

if ( settings.client_logo_hover !== '' ) {
	item_class.push( 'ube-client-logo-hover-' + settings.client_logo_hover);
}

if ( settings.client_logo_content_alignment !== '' ) {
	item_class.push( settings.client_logo_content_alignment);
}

var tag_html = 'div';
if ( settings.client_logo_link.url !== '' ) {
		tag_html = 'a';
		if( settings.client_logo_link.is_external){
		view.addRenderAttribute( 'item_class', 'target', '_blank' );
		}
		if(settings.client_logo_link.nofollow){
		view.addRenderAttribute( 'item_class', 'rel', 'nofollow' );
		}
		view.addRenderAttribute( 'item_class','href' ,ubeSafeUrl( settings.client_logo_link.url ) );
}

view.addRenderAttribute( 'item_class', 'class', item_class );

#>

<div {{{ view.getRenderAttributeString( 'client_logo_classes' ) }}}>
<{{{tag_html}}} {{{ view.getRenderAttributeString( 'item_class' ) }}}>
	<#	if ( settings.client_logo_logo.url !== '' ) { #>
		<img src="{{{ ubeEscHtml( ubeSafeUrl( settings.client_logo_logo.url ) ) }}}">
	<# }#>
</{{{tag_html}}}
</div>