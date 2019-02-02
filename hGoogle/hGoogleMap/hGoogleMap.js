var google = {
    map : null,

    ready : function()
    {
        this.map = new google.maps.Map(
            $(this.mapSelector).get(0), {
                center : new google.maps.LatLng(this.initialLatitude, this.initialLongitude),
                zoom : this.initialZoomLevel,
                mapTypeId : google.maps.MapTypeId.ROADMAP
            }
        );
    }
};

$(document).ready(
    function()
    {
        google.ready();
    }
);
