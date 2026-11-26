<#
var ubeEscHtml = function( str ) {
	return ( str === undefined || str === null ? "" : String( str ) )
		.replace( /&(?!(?:#[0-9]+|#x[0-9a-fA-F]+|[a-zA-Z][a-zA-Z0-9]*);)/g, "&amp;" )
		.replace( /</g, "&lt;" ).replace( />/g, "&gt;" )
		.replace( /"/g, "&quot;" ).replace( /'/g, "&#039;" );
};
view.addInlineEditingAttributes( 'text', 'basic' );
view.addRenderAttribute('button', 'class', 'btn btn-primary');
view.addRenderAttribute('icon', 'class', settings.icon.value);
#>
<div {{{ view.getRenderAttributeString( 'button' ) }}}>
	<i {{{ view.getRenderAttributeString( 'icon' ) }}}></i>
	<span {{{ view.getRenderAttributeString( 'text' ) }}}>{{{ ubeEscHtml( settings.text ) }}}</span>
</div>