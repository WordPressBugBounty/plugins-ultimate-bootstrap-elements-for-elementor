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
view.addInlineEditingAttributes( 'badge_text', 'basic' );
var badge_classes = [
	'badge',
	settings.badge_view,
];
if (settings.badge_color_scheme !== '') {
	badge_classes.push('badge-' + settings.badge_color_scheme);
}
view.addRenderAttribute('badge_text', 'class', badge_classes);

var badge_tag = 'span';
var link = '';
if ( settings.badge_link.url) {
	badge_tag = 'a';
	link = 'href="' + ubeEscHtml( ubeSafeUrl( settings.badge_link.url ) ) + '"';
}
#>
<{{{badge_tag}}} {{{ view.getRenderAttributeString( 'badge_text' ) }}} {{{link}}}>
	{{{ ubeEscHtml( settings.badge_text ) }}}
</{{{badge_tag}}}>