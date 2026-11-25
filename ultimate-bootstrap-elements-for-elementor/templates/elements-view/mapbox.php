<#
var ubeSafeUrl = function( url ) {
	url = ( url || "" ) + "";
	var scheme = _.filter( url.split( "" ), function( c ) { return c.charCodeAt( 0 ) > 32; } ).join( "" ).match( /^([a-z][a-z0-9+.-]*):/i );
	return ( ! scheme || /^(https?|ftps?|mailto|tel|sms|fax)$/i.test( scheme[1] ) ) ? url : "";
};
var wrapper_classes =[
	'ube-map-box',
];

if (settings.marker_effect=== 'yes') {
	wrapper_classes.push( 'ube-map-box-effect');
}

view.addRenderAttribute('wrapper','class',wrapper_classes);


var id = 'ube_map_box_' + view.getIDInt().toString();

view.addRenderAttribute('wrapper','id', id);

var options = {
	"scrollwheel" : settings.zoom_mouse_wheel=== 'yes',
	"skin"       : settings.map_style,
	"container" : id
};

if ( settings.map_zoom!== '' ) {
	options.zoom= settings.map_zoom;
}



view.addRenderAttribute('wrapper','data-options',JSON.stringify(options));
var markers = [];
_.each( settings.items, function(value ) {
	var address     =value.address ?  value.address : '';
	var position = {};
	if (address!=='' && address.indexOf(',') > 0 ) {
		address_arr = address.split( ',');
        position.lat= address_arr[0];
        position.lng= address_arr[1];
	}
	var marker_html = '';
	var marker_url = ubeSafeUrl( value.image_marker.url );
	if (marker_url!=='') {
		marker_html = '<img src="'+ _.escape( marker_url ) +'">';
	}

    var popup_html                = '<div class="card">';
    var popup_image_url = ubeSafeUrl( value.image.url );
    if (popup_image_url!==''){
      popup_html+='<img src="'+ _.escape( popup_image_url ) +'">';
    }
    popup_html+='<div class="card-body">';
     if (value.title!==''){
       popup_html+=  '<h5 class="card-title">'+ _.escape( value.title ) +'</h5>';
    }

    if (value.description!==''){
         popup_html+= '<p class="card-text">'+ _.escape( value.description ) +'</p>';
    }
    popup_html+=  '</div>';
    popup_html+='</div>';

	markers.push( {
		"position" : position,
		"marker"   : marker_html,
		"popup"    : popup_html
        });
});
view.addRenderAttribute('wrapper','data-markers',JSON.stringify(markers));
#>
<div {{{ view.getRenderAttributeString('wrapper' )}}} ></div>
