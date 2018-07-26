var PageEditor = new function()
{
    var session;
    
    //Box actual
    var currentBox;
    
    var dialog;
    var dialogContent;
    
    //Al agregar un box, se registra aqui el boton "+"
    var lastPosition;
    
    var peBaseUrl;
    
    //Editor activo?
    var editor = false;
    
    //User permission
    var userPermissions;
    
    //Para usar en contextos donde this no referencia a PageEditor
    var self;
    
    //Mutex para guardado
    var savingMutex;
    
    //Boton de publicar
    var publishBtnContent = "<span class='glyphicon glyphicon-send'></span> Publish";
    var publishBtnWaitContent = "<span class='glyphicon glyphicon-send'></span> Wait...";
    
    //Boton de editar
    var editBtnContent = "<span class='glyphicon glyphicon-pencil'></span> Edit";
    
    this.init = function()
    {
        self = this;
        
        session = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        });
        
        $('.wrap').css('padding-bottom','120px');
        
        //Boxes
        $('body').on('click', '[data-configure]', function(e){ actionStartConfiguration($(this)); });
        $('body').on('click', '[data-delete]', function(e){ actionDeleteBox($(this)); });
        
        //Plus
        $('body').on('click', '[data-add-box]', function(e){ actionAddBox($(this)); });
        $('body').on('click', '[data-search]', function(e){ actionSearchBoxes($(this)); });

        //Toolbar
        $('body').on('click', '[data-save-page]', function(e){ saveCols(); });
        $('body').on('click', '[data-edit]', function(e){
            if(typeof ModelEdition  != "undefined"){
                if (!ModelEdition.active){
                    ModelEdition.begin();
                }else{
                    PageEditor.edit();
                }
            }else{
                PageEditor.edit();
            }

        });
        
        userPermissions = _EL.userPermissions;
        
        var notSortable = ':first-child';
        if(userPermissions < 4){
            notSortable += ',[data-mode=0],[data-mode=1],[data-mode=2],[data-mode=3]';
        }
        
        $('[data-col]').sortable({
            items: '>div:not('+notSortable+')',
            placeholder: 'sortable-placeholder',
            cancel: '[contenteditable="true"]'
        });
        $('[data-col]').sortable('disable');
        
        peBaseUrl = _EL.baseUrl;
        
        initToolbar();
    }
    
    function initToolbar()
    {
        var thtml = "<div class='navbar navbar-default navbar-fixed-bottom'>\
            <div class='container-fluid'>\
                <div class='navbar-header'>\
                    <button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#c-bottom-er' aria-expanded='false'>\
                      <span class='icon-bar'></span><span class='icon-bar'></span><span class='icon-bar'></span>\
                    </button>\
                </div>\
                <div class='collapse navbar-collapse' id='c-bottom-er'>\
                    <ul class='nav navbar-nav pull-right'>\
                        <li><a class='btn btn-primary navbar-btn btn-sm' data-edit style='margin-right:10px;'>"+editBtnContent+"</a></li>\
                        <li><a class='btn btn-success navbar-btn btn-sm' data-save-page>"+publishBtnContent+"</a></li>\
                    </ul>\
                </div>\
            </div>\
        </div>";
        var toolbar = $('body').append(thtml);
    }
    
    function plusButton()
    {
        var plus = '<div data-search data-action="'+peBaseUrl+'/box/search" class="btn btn-default" style="width:100%; text-align:center; background-color: #e9e9e9; margin-bottom: 10px;">\
                    <span class="glyphicon glyphicon-plus"></span></div>';
        
        return plus;
    }
    
    function boxButtons(editable, deletable)
    {
        var edit = editable || '2367'.indexOf(userPermissions)>=0;
        var del = deletable || '1357'.indexOf(userPermissions)>=0;
        
        var configBtn = edit ? '<a href="#" data-configure><span class="glyphicon glyphicon-pencil" title="Config"></span></a>' : '';
        var deleteBtn = del ? '<a href="#" data-delete><span class="glyphicon glyphicon-trash"></span></a>' : '';
        
        return '<div class="box-editor-bar">'+configBtn+' '+deleteBtn+'</div>';

    }
    
    var editMutex;
    this.edit = function()
    {
        if(savingMutex === true || editMutex === true){
            return;
        }
        editMutex = true;
        
        $('[data-edit]').css('opacity',0.5);
        
        if(editor === false){
            
            //Chequeamos contra una columna si corresponde con la última versión
            var col = $('[data-col]').first().attr('data-col');
        
            $.ajax({
                method: 'post',
                url: peBaseUrl+'/col/check-version',
                data: {id: col},
                xhrFields: {
                    withCredentials: true
                },
                crossDomain: true
            }).done(function(r){

                if(r.status == 'error'){
                    alert(r.error);
                    return;
                }

                //Si no es la última versión, recargamos la página
                if(col != r.last){

                    bootbox.confirm({
                        message: "A new version of the page has been published. You have to refresh the page to edit. <br><br> Do you want to do it now?",
                        buttons: {
                            confirm: {
                                label: 'Yes',
                                className: 'btn-success',
                            },
                            cancel: {
                                label: 'No',
                                className: 'btn-danger'
                            }
                        },
                        callback: function(result){
                            if(result === true){
                                location.reload(true);
                            }
                        }
                    });

                }else{
                    
                    var plus = plusButton();

                    $('[data-col]').prepend(plus);
                    $('[data-box]').each(function(index,el){

                        /*
                         * El modo se almacena en tres posiciones de binario (sed). S=sortable;E=editable;D=deletable.
                         * la forma mas simple con js (que encontre) para verificar los permisos, es buscar entre
                         * los modos permitidos.
                         */
                        var mode = $(el).attr('data-mode'),
                            editable = '2367'.indexOf(mode)>=0,
                            deletable = '1357'.indexOf(mode)>=0;

                        $(this).prepend(boxButtons(editable,deletable));
                        $(this).append(plus);
                    });

                    $('[data-col]').sortable('enable');

                    $('[data-edit]').html('<span class="glyphicon glyphicon-eye-open"></span> Preview');

                    //Prevenimos que se abandone la pagina
                    $('body').on('click', '[data-col] a', function(e){e.preventDefault();});

                    $('body').addClass('editing-page');

                    editor = !editor;
                }
            }).always(function(){
                editMutex = false;
                $('[data-edit]').css('opacity',1);
            });
            
        }else{
            $('[data-search]').hide(200, function(){$(this).remove();});
            $('[data-col]').sortable('disable');
            $('.box-editor-bar').hide(200, function(){$(this).remove();});

            $('[data-edit]').html('<span class="glyphicon glyphicon-pencil"></span> Edit');

            $('body').off('click', '[data-col] a');

            $('body').removeClass('editing-page');
            
            editor = !editor;
            
            editMutex = false;
            $('[data-edit]').css('opacity',1);
        }

    }
    
    /**
     * Carga el form para editar un box en un modal
     */
    var startConfigMutex = false;
    function actionStartConfiguration($btn)
    {
        
        if(startConfigMutex === true){
            return;
        }
        startConfigMutex = true;
        
        $box = $btn.closest('[data-box]');
        $btn.css('opacity',0.5);
        
        var data = {
            box_id: $box.attr('data-id'),
            session: session
        }
        
        $.ajax({
            method: 'post',
            url: peBaseUrl+'/box/configure',
            data: data,
            xhrFields: {
                withCredentials: true
            },
            crossDomain: true
        }).done(function(r){
            if(r.status == 'success'){
                currentBox = r.box;
                showForm(r.form);
            }
        }).always(function(){
            startConfigMutex = false;
            $btn.css('opacity',1);
        });
    }
    
    /**
     * Envia la config de un box para ser guardada
     */
    var commitConfigMutex = false;
    function commitConfiguration()
    {
        
        if(commitConfigMutex === true){
            return;
        }
        commitConfigMutex = true;
        
        var url = peBaseUrl+'/box/configure';
        var data = $('#form-container form').serializeArray();
        data.push({name: 'session', value: session});
        data.push({name: 'box_id', value: currentBox.box_id});
        
        $.ajax({
            method: 'post',
            data: data,
            url: url,
            xhrFields: {
                withCredentials: true
            },
            crossDomain: true,
            beforeSend: function(){
                $('#form-container').addClass('loading').css('opacity',0.5);
                $('[data-box][data-id='+currentBox.box_id+']').addClass('loading');
            },
        }).done(function(r){
            if(r.status == 'success'){
                loadBox(r.box, r.view);
                bootbox.hideAll();
            }else{
                $('#form-container').html(r.form);
            }
        }).always(function(){
            commitConfigMutex = false;
            $('#form-container').removeClass('loading').css('opacity',1);
            $('[data-box][data-id='+currentBox.box_id+']').removeClass('loading');
        });
    }
    
    //Manual
    this.sendConfiguration = function(box_id, data)
    {
        
        var url = peBaseUrl+'/box/configure';
        data.push({name: 'session', value: session});
        data.push({name: 'box_id', value: box_id});
        
        $.ajax({
            method: 'post',
            data: data,
            url: url,
            xhrFields: {
                withCredentials: true
            },
            crossDomain: true,
            beforeSend: function(){
                $('[data-box][data-id='+box_id+']').addClass('loading');
            }
        }).done(function(r){
            if(r.status == 'success'){
                loadBox(r.box, r.view);
            }
        }).always(function(){
            $('[data-box][data-id='+box_id+']').removeClass('loading');
        });
    }
    
    //Carga/recarga un box en la posicion del div con data-box y data-id=BOX_ID
    function loadBox(box, view)
    {
        $('[data-box][data-id='+box.box_id+']').replaceWith(view);
        
        $('[data-box][data-id='+box.box_id+']').prepend(boxButtons(box.boxEditable, box.boxDeletable))
        $('[data-box][data-id='+box.box_id+']').append(plusButton());
    }
    
    function showForm(form)
    {
        dialogContent = $('<div id="form-container"></div>');
        $form = $(form);
        dialogContent.html($form);
        
        $form.find('input').not('.search-box input').on('keydown',function(e){
            if(e.keyCode == 13) {
                e.preventDefault();
                commitConfiguration();
                return false;
            }
        });
        
        dialog = bootbox.dialog({
            size: 'large',
            title: 'Config...',
            message: dialogContent,
            buttons: configButtons(),
        });
    }
    
    function actionSearchBoxes($btn)
    {
        lastPosition = $btn.closest('[data-box]');
        
        if(!lastPosition.length){
            lastPosition = $btn;
        }
        
        $content = $('<div>Loading...</div>');
        
        dialog = bootbox.dialog({
            size: 'large',
            title: 'Search',
            message: $content
        });
        
        var colId = lastPosition.closest('[data-col]').attr('data-col');

        $.ajax({
            method: 'post',
            data: {
                col: colId
            },
            url: $btn.attr('data-action'),
            xhrFields: {
                withCredentials: true
            },
            crossDomain: true,
        }).done(function(r){
            if(r.status == 'success'){
                $content.html(r.list);
            }
        });
    }
    
    var addBoxMutex = false;
    function actionAddBox($btn)
    {
        
        if(addBoxMutex === true){
            return;
        }
        addBoxMutex = true;
        
        $content = $('<div>Loading...</div>');
        
        $.ajax({
            method: 'post',
            data: {
                class: $btn.attr('data-class'),
                session: session
            },
            url: $btn.attr('data-action'),
            xhrFields: {
                withCredentials: true
            },
            crossDomain: true,
        }).done(function(r){
            if(r.status == 'success'){

                currentBox = r.box;
                
                /**
                 * Al abrir rapidamente otro modal (showForm mas abajo), la clase
                 * modal-open del body se pierde, generando errores, por lo que
                 * la agregamos al ocultarse el modal:
                 */
                dialog.on('hidden.bs.modal', function () {
                    $("body").addClass("modal-open");
                });
                dialog.modal('hide');
                lastPosition.after($('<div data-box data-id="'+r.box.box_id+'"></div>'));
                
                //Insertamos en la posicion botón "+" y box
                loadBox(r.box, r.view);
                
                if (r.form !== null) {
                    showForm(r.form);
                }
            }
        }).always(function(){
            addBoxMutex = false;
        });
    }
    
    function configButtons()
    {
        return {
            confirm: {
                label: 'Save',
                className: 'btn-success',
                callback: function (result) {
                    commitConfiguration();
                    return false;
                }
            },
            cancel: {
                label: 'Cancel',
                className: 'btn-danger'
            }
        };
    }
    
    function actionDeleteBox($btn)
    {
        
        bootbox.confirm({
            message: "Remove box?",
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-success',
                },
                cancel: {
                    label: 'No',
                    className: 'btn-danger'
                }
            },
            callback: function(result){
                if(result === true){
                    deleteBox( $btn.closest('[data-box]') );
                }
            }
        });
        
    }
    
    function deleteBox($box)
    {
        $box.css('background-color', '#999');
        $box.next('[data-search]').css('background-color', '#999');
        $box.next('[data-search]').hide(1200, function(){$(this).remove()});
        $box.hide(300, function(){$(this).remove()});
    }
    
    function saveCols()
    {
        
        if(savingMutex === true){
            return;
        }
        savingMutex = true;
        
        var cols = [];
        $('[data-col]').each(function(){
            
            var boxes = [];
            $(this).find('[data-box]').each(function(index){
                boxes[index] = $(this).attr('data-id');
            });
            
            var col = {
                id: $(this).attr('data-col'),
                boxes: boxes
            };
            cols.push(col);
        });
        
        var data = {
            session: session,
            cols: cols
        };
        
        $.ajax({
            url: peBaseUrl+'/col/save',
            data: data,
            method: 'post',
            beforeSend: function(){
                $('[data-save-page]').removeClass('btn-success').addClass('btn-danger');
                $('[data-save-page]').html(publishBtnWaitContent);
            },
            xhrFields: {
                withCredentials: true
            },
            crossDomain: true,
        }).done(function(r){
            if(r.status == 'success'){
                bootbox.alert({
                    message: "Page has been saved.",
                    callback: function(result){
                        location.reload(true);
                    }
                });
            }else{
                bootbox.alert('An error has ocurred.');
            }
        }).always(function(){
            savingMutex = false;
            $('[data-save-page]').removeClass('btn-danger').addClass('btn-success');
            $('[data-save-page]').html(publishBtnContent);
            
            if(editor == true){
                self.edit();
            }
        });
        
    }
    
    this.getCurrentBox = function()
    {
        return currentBox;
    }
    
    this.editing = function()
    {
        return editor ? true : false;
    }
}
PageEditor.init();