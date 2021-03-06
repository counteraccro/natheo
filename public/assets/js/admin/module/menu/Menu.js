/**
 *  JS gestion des menu pour l'admin
 *  @author Gourdon Aymeric
 *  @version 1.0
 **/
let Menu = {}

Menu.Launch = function () {

    Menu.globalId = '#admin-menu-globale';

    /**
     * Charge la liste des tags
     * @constructor
     */
    Menu.LoadListing = function () {
        let id = Menu.globalId + ' .card-body';
        let url = $(id).data('url');

        let str_loading = $(id).data('loading');
        System.Ajax(url, id, true, str_loading);
    };

    /**
     * Event sur l'ajout / edition d'un role
     * @constructor
     */
    Menu.CreateUpdate = function () {

    }

    /**
     * Event sur les elements du menu
     * @constructor
     */
    Menu.EventMenuElements = function (menuElementGlobalId) {

        $(menuElementGlobalId + ' #btn-add-menu-element').click(function () {
            Menu.loadModal($(this));
        });
    }

    /**
     * Event sur la modale d'ajout / edition de menu element
     * @constructor
     */
    Menu.EventModalMenuElement = function (modal) {
        /**
         * Affiche ou masque le champ url et les autres champs dépendants en fonction de isDirectLink
         * @constructor
         */
        Menu.ShowUrlInput = function () {
            if (!$('#menu_element_isDirectLink').prop('checked')) {
                $('#menu_element_url').parent().hide();
                $('#menu_element_page').parent().show();
                $('#menu_element_slug').parent().hide();
            } else {
                $('#menu_element_url').parent().show();
                $('#menu_element_page').parent().hide();
                $('#menu_element_slug').parent().hide();
            }
        }

        /**
         * Permet de charger la liste de position en fonction d'un parent
         * @constructor
         */
        Menu.LoadPosition = function (param) {

            if (param === "") {
                param = 0;
            }

            let id = ' #modal-create-update-menu-element #field_position';
            let url = $(id).data('url');

            url = url.replace('element-parent', param);

            let str_loading = $(id).data('loading');
            System.Ajax(url, id, true, str_loading);
        }

        /**
         * Event sur le changement de la checkbox
         */
        $('#modal-create-update-menu-element #menu_element_isDirectLink').change(function () {
            Menu.ShowUrlInput();
        })

        /**
         * Event sur le choix d'un element parent
         */
        $('#modal-create-update-menu-element #menu_element_parent').change(function () {
            Menu.LoadPosition($(this).val());
        })

        /**
         * Event sur le choix d'une page (pour l'url)
         */
        $('#modal-create-update-menu-element #menu_element_page').change(function () {

            let val = $(this).val();
            if (val === "") {
                val = 0;
            }

            let url = $(this).data('url');
            url = url.replace('id-page', val);

            $.ajax({
                method: 'GET',
                url: url,
            })
                .done(function (html) {
                    if(html.url === "")
                    {
                        $('#modal-create-update-menu-element #menu_element_slug').parent().hide();
                        let help = $('#modal-create-update-menu-element #menu_element_slug').data('help');
                        $('#modal-create-update-menu-element #menu_element_slug_help').html(help).removeClass('text-danger');
                        $('#modal-create-update-menu-element #menu_element_slug').val('');
                    }
                    else {
                        $('#modal-create-update-menu-element #menu_element_page_help').html(html.url);
                        $('#modal-create-update-menu-element #menu_element_slug').parent().show();
                    }

                });
        })

        /**
         * Event sur le champ slug
         */
        $('#modal-create-update-menu-element #menu_element_slug').change(function () {

            if ($(this).val() === "") {
                $('#modal-create-update-menu-element #menu_element_page').trigger('change');
                return false;
            }

            let slug = System.stringToSlug($(this).val());
            let help = $(this).data('help');

            let url = $(this).data('url');
            url = url.replace('param-slug', slug);

            $.ajax({
                method: 'GET',
                url: url,
            })
                .done(function (response) {

                    if(response.success === false)
                    {
                        $('#modal-create-update-menu-element #menu_element_slug_help').html(response.msg).addClass('text-danger');
                    }
                    else {

                        $('#modal-create-update-menu-element #menu_element_slug_help').html(help).removeClass('text-danger');

                        let url = $('#modal-create-update-menu-element #menu_element_page_help').html();
                        let str = url.substr(url.lastIndexOf('/') + 1) + '$';
                        url = url.replace(new RegExp(str), slug);
                        $('#modal-create-update-menu-element #menu_element_page_help').html(url);
                    }
                });
        })

        /**
         * Au submit du formulaire
         */
        $('#modal-create-update-menu-element #form-create-update-menu-element').submit(function (e) {
            e.preventDefault();

            $('#form-create-update-menu-element').loader($(this).data('loading'));

            $.ajax({
                method: 'POST',
                data: $(this).serialize(),
                url: $(this).attr('action'),
            })
                .done(function (html) {
                    modal.hide();
                    $(System.adminBlockModalId).html(html);
                });

        });

        Menu.ShowUrlInput();
    }


    /**
     * Permet de charger le rendu du menu exemple
     * @constructor
     */
    Menu.LoadExempleRender = function (param) {
        let id = ' #admin-create-update-menu #exemple-render';
        let url = $(id).data('url');

        if (param !== '') {
            url = url + '/' + param;
        }

        let str_loading = $(id).data('loading');
        System.Ajax(url, id, true, str_loading);
    }

    /**
     * Permet de charger les elements d'un menu
     * @constructor
     */
    Menu.LoadMenuElement = function () {
        let id = ' #admin-create-update-menu #block-element';
        let url = $(id).data('url');

        let str_loading = $(id).data('loading');
        System.Ajax(url, id, true, str_loading);
    }

    /** Event pour le changement des exemples de rendu **/
    Menu.SwitchExempleRender = function () {
        $('#admin-create-update-menu #select-render-menu').change(function () {
            let val = $(this).val();
            Menu.LoadExempleRender(val);
        })
    }

    /**
     * Permet de charger le contenu d'une popin
     * @param element
     */
    Menu.loadModal = function (element) {

        let url = element.data('url');
        let str_loading = element.data('loading');
        let id = System.adminBlockModalId;

        $('body').loader(str_loading);

        $.ajax({
            method: 'GET',
            url: url,
        })
            .done(function (html) {
                $('body').removeLoader(str_loading);
                $(System.adminBlockModalId).html(html);
            });
    }
}