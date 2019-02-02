var user = {
    formSubmission : false,

    ready : function()
    {
        $('input#hUserFormSubmit').click(
            function(e)
            {
                if ($('input#hUserEmail').val() && !user.formSubmission && !$('input#hUserSecurityAnswer').nextAll('p.hFormError').length)
                {
                    e.preventDefault();
                    user.validate();
                }
            }
        );
    },

    validate : function()
    {
        http.post(
            '/hUser/hUserLogin/getSecurityQuestion', {
                operation : 'Get Security Question',
                hUserEmail : $('input#hUserEmail').val()
            },
            function(json)
            {
                user.formSubmission = true;

                $('label[for="hUserSecurityAnswer"]').text(json + ':');
                $('fieldset#hUserSecurityQuestionFieldset').slideDown('slow');
            }
        );
    }
};

$(document).ready(
    function()
    {
        user.ready();
    }
);