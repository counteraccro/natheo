/**
 *  JS gestion des roles
 *  @author Gourdon Aymeric
 *  @version 1.0
 **/
let Role = {}

Role.Launch = function() {

    Role.globalId = '#admin-role-globale';

    /**
     * Charge la liste des routes
     * @constructor
     */
    Role.LoadListing = function()
    {
        let id = Role.globalId + ' .card-body';
        let url = $(id).data('url');

        let str_loading = $(id).data('loading');
        System.Ajax(url, id, true, str_loading);
    };

    /**
     * Event sur l'ajout / edition d'un role
     * @constructor
     */
    Role.CreateUpdate = function() {

        Role.createUpdateId = '#admin-create-update-role';
        $(Role.createUpdateId + ' #render-exemple-short-role').css({"background-color" : $(Role.createUpdateId + ' #role-color').val()});
        $(Role.createUpdateId + ' #render-exemple-short-role').html( $(Role.createUpdateId + ' #role-short-label').val());
        Role.updateHidenInputRoute();
        Role.ShowModuleCheck();

        /**
         * Au changement du short label, on met à jour l'exemple
         */
        $(Role.createUpdateId + ' #role-short-label').keyup(function() {
            $(Role.createUpdateId + ' #render-exemple-short-role').html($(this).val());
        });

        /**
         * AU changement de la couleur on met à jour l'exemple
         */
        $(Role.createUpdateId + ' #role-color').change(function() {
            $(Role.createUpdateId + ' #render-exemple-short-role').css({"background-color" : $(this).val()});
        });

        /**
         * Evenement pour checker toute les checkbox d'un module
         */
        $(Role.createUpdateId + ' .role-checkbox-all').change(function() {

            let id = $(this).parent().data('bs-target');
            let checkId = $(this).data('class');
            let show = false;

            if($(this).is(':checked'))
            {
                show = true;
            }

            $(Role.createUpdateId + ' .' + checkId).each(function() {
                $(this).prop('checked', show);
            })

            let myCollapse = document.getElementById(id.substr(1));
            let bsCollapse = new bootstrap.Collapse(myCollapse, {
                toggle: show
            });
            Role.updateHidenInputRoute();
        });

        /**
         * Event sur une check box
         */
        $(Role.createUpdateId + ' .role-checkbox').change(function() {
            Role.updateHidenInputRoute();
            Role.ShowModuleCheck();
        });
    }

    /**
     * Met à jour la check box de chaque module si toute les check box de celui ci sont coché
     * @constructor
     */
    Role.ShowModuleCheck = function()
    {
        $(Role.createUpdateId + ' .role-checkbox-all').each(function() {

            let element = $(this);
            let nb_elements = $(Role.createUpdateId + ' .' + element.data('class')).length;
            let nb_check = 0;

            $(Role.createUpdateId + ' .' + $(this).data('class')).each(function() {

                if($(this).is(':checked'))
                {
                   nb_check++;
                }
            });

            element.prop('checked', false);
            if(nb_check === nb_elements)
            {
                element.prop('checked', true);
            }
        });
    }



    /**
     * Permet de remplir le champs masqué d'id pour l'attributions des routes pour le role
     */
    Role.updateHidenInputRoute = function()
    {
        let str_id = '';
        $(Role.createUpdateId + ' .role-checkbox').each(function() {
            if($(this).is(':checked'))
            {
                str_id += $(this).data('route-id') + '-';
            }
        });
        $(Role.createUpdateId + ' #hidden-route-id').val(str_id.substr(0, str_id.length - 1));
    }
}