# README

## yii2-page-editor

* Editor de páginas para Yii2 de Quoma
* 0.1.0

Se debe implementar un action en algun controlador para cargar los assets de
edición (js y css). Este action debe validar si el usuario se encuentra logueado
en el backend, y en caso de ser asi, enviar los assets. En caso contrario, la
respuesta debe ser vacia. La url de este action debe ser cargada en la
configuración del módulo, en el atributo assetsUrl.