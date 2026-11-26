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
view.addRenderAttribute( 'ube_image_marker_attr', 'class', 'ube-marker-wrapper' );
if ( settings.image_marker_animation== 'yes' ) {
view.addRenderAttribute( 'ube_image_marker_attr', 'class', 'ube-marker-animate-icon' );
}
if ( settings.image_marker_arrow == 'yes' ) {
view.addRenderAttribute(  'ube_image_marker_attr', 'class', 'ube-marker-tooltip-arrow' );
}
#>
<div {{{ view.getRenderAttributeString( 'ube_image_marker_attr' ) }}}>
<#
var image = {
id: settings.image.id,
url: settings.image.url,
size: settings.image_size_size,
dimension: settings.image_size_custom_dimension,
model: view.getEditModel()
};
var image_url = elementor.imagesManager.getImageUrl( image );
if(!image_url){
return;
}
var tabindex = view.getIDInt().toString().substr( 0, 3 );
var marker_tag = 'div';
#>
<img src="{{{ ubeEscHtml( image_url ) }}}"/>
<#
if(settings.image_marker_list){
_.each( settings.image_marker_list, function( item, index ) {
var title = '';
if ( item.marker_title!=='' ) {
title += '<h4>' + ubeEscHtml( item.marker_title ) + '</h4>';
}
if (item.marker_content!=='' ) {
title += '<p>' + ubeEscHtml( item.marker_content ) + '</p>';
}
view.addRenderAttribute( 'marker_attr_' + index, 'title', title );

var marker_link_url = ubeSafeUrl( item.marker_link.url );
if ( marker_link_url ) {
marker_tag = 'a';
var target     = item.marker_link.is_external ? ' target="_blank"' : '';
var nofollow   = item.marker_link.nofollow ? ' rel="nofollow"' : '';
view.addRenderAttribute( 'marker_attr_' + index, 'href', marker_link_url);
view.addRenderAttribute( 'marker_attr_' + index, 'target', target );
view.addRenderAttribute( 'marker_attr_' + index, 'rel', nofollow );
}
var marker_classes = 'ube-image-pointer elementor-repeater-item-'+ item._id;
view.addRenderAttribute( 'marker_attr_'+ index, 'class', marker_classes );
var iconHTML = elementor.helpers.renderIcon( view, item.marker_icon, { 'aria-hidden': true }, 'i' , 'object' );
#>
<{{{ marker_tag }}} {{{view.getRenderAttributeString( 'marker_attr_' + index)}}}>
<div class="ube-pointer-icon">
    <#
    if ( item.type_of_marker === 'icon' ) {
    if ( item.marker_icon.value ) {
    #>
    <span class="ube-icon">
    {{{ iconHTML.value }}}
        </span>
    <#}
    } else {
    #>
    {{{ ubeEscHtml( item.marker_text ) }}}
    <#
    }
    #>
</div>
</{{{ marker_tag }}}>
<# });
}
#>
</div>
