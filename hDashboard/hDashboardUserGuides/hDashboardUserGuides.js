var userGuides = {
    ready : function()
    {
        $('a.hDashboardUserGuideScreenshotLink').attr('target', '_blank');
    }
};

$(document).ready(
    function()
    {
        userGuides.ready();
    }
);
