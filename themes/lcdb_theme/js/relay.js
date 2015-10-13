
var map,
    directionsService   = new google.maps.DirectionsService(),
    zoomLevel           = 12,
    infowindow          = null,
    infoBox             = null,
    defaultLat          = 48.856358,
    defaultLon          = 2.351761,
// $documentFragment   = $j190(document.createDocumentFragment());
    $documentFragment   = $j190('<div>');

function relayInitialize() {

    infoBox = new InfoBox({
        content: '...'
    });
    var relaysLength    = relays.length,
        $relayList      = $j190('#relay-list');

    //options of the map
    var opt = {
        zoom        : zoomLevel,
        center      : new google.maps.LatLng(defaultLat, defaultLon),
        mapTypeId   : google.maps.MapTypeId.ROADMAP
    };

    //starts the map
    map = new google.maps.Map(document.getElementById('map'), opt);

    //creates the markers and the list on the left
    for (var i=0; i< relaysLength; i++) {
        createMarker(i, relays[i].name, relays[i].address, relays[i].tel, relays[i].description, relays[i].lat, relays[i].lon, relays[i]);
        var $listItem   = $j190(document.createElement('li')),
            $title      = $j190(document.createElement('h3')),
            $address1   = $j190(document.createElement('p')),
            $address2   = $j190(document.createElement('p')),
            $city       = $j190(document.createElement('p')),
            $tel        = $j190(document.createElement('p')),
            $mention    = $j190(document.createElement('p')),
            $description       = $j190(document.createElement('p')),
            $wrapper    = $j190(document.createElement('div')),
            $choose     = $j190(document.createElement('a')),
            $marker     = $j190(document.createElement('span')),
            $separation = $j190(document.createElement('hr'));

        $title.text(relays[i].name).addClass('relay-name');
        $address1.text(relays[i].address[0]).addClass('relay-address');
        $address2.text(relays[i].address[1]).addClass('relay-address');
        $city.text(relays[i].postcode + ' ' + relays[i].city).addClass('relay-city');
        $tel.text(relays[i].tel);
        $mention.text(relays[i].mention).addClass('relay-mention');
        $description.text(relays[i].description).addClass('relay-description');
        $choose.text('choisir ce point relais').attr({'href': '#', 'title': 'choisir ce point relais'}).addClass('green-button choose-relay');
        $wrapper.append($choose);
        $listItem.append($title, $address1, $address2, $city, $tel, $mention, $description, $wrapper, $marker, $separation);
        if (i===0)
            $listItem.addClass('first');

        if (i===relaysLength-1)
            $listItem.addClass('last');

        $marker.addClass('marker'+(i+1));
        $documentFragment.append($listItem);
        $($listItem[0]).data('relay',relays[i]);
    }
    $relayList.append($documentFragment.children());
}

img_folder ='';

function createMarker(index, name, adresse, telephone, mention, lat, lon, data){
    var markerOptions = {
        draggable   : false,
        Animation   : google.maps.Animation.DROP,
        map         : map,
        center      : new google.maps.LatLng(lat, lon),
        position    : new google.maps.LatLng(lat, lon),
        icon        : new google.maps.MarkerImage(img_folder+'/markers.png', new google.maps.Size(20, 34, "px", "px"), new google.maps.Point(0, (index*34+1)), null, null)
    };

    m = new google.maps.Marker(markerOptions);

    google.maps.event.addListener(m, 'click', function () {
        var cont = document.createElement("div");
        var html_code = '';
        if(typeof name != "undefined")
            html_code += '<h3 class="relay-name">'+name+'</h3>';
        if(typeof adresse[0]!= "undefined")
            html_code += '<p class="relay-address">'+adresse[0]+'</p>';
        if(typeof adresse[1] != "undefined")
            html_code += '<p class="relay-address">'+adresse[1]+'</p>';
        if(typeof adresse[2] != "undefined")
            html_code += '<p class="relay-address">'+adresse[2]+'</p>';
        if(typeof telephone != "undefined")
            html_code += '<p>'+telephone+'</p>';
        if(typeof mention != "undefined")
            html_code += '<p class="relay-mention">'+mention+'</p>';

        html_code += '<br><center><a style="font-size:0.7em;" id="choose-relay2" onclick="choose_relay2(this);" class="green-button" href="#" title="choisir ce point relais">choisir ce point relais</a></center>';
        $(cont).data('relay',data);
        $j190(cont).html(html_code);
//        $j190(cont).html('<h3 class="relay-name">'+name+'</h3><p>'+adresse[0]+'</p><p>'+adresse[1]+'</p><p>'+telephone+'</p><p class="relay-mention">'+mention+'</p>');
        infoBox.setOptions({
            alignBottom             : true,
            disableAutoPan          : true,
            content                 : cont,
            pixelOffset             : new google.maps.Size(-100, -40),
            zIndex                  : 4000,
            boxClass                : 'infoBox',
            closeBoxURL             : img_folder+'close.gif',
            isHidden                : false,
            pane                    : 'floatPane',
            enableEventPropagation  : true
        });
        infoBox.open(map, this);
        map.panTo(infoBox.position_);



    });


}
