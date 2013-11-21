/**
 * Created with JetBrains PhpStorm.
 * User: Adriano
 * Date: 12/01/13
 * Time: 08:51
 * To change this template use File | Settings | File Templates.
 */
Folder = {

    allFolders : {},
    bayKey: {},

    get: function( filter, force ){

        return ( ( filter === false ) ? Folder.getList( force ) : Folder.getFolder( filter, force ) )

    },

    getList: function( force ){

        if( force == true || $.isEmptyObject( Folder.allFolders )  ){

            Folder.refresh();

        }

        return $.extend([], Folder.allFolders);    
    },

    getFolder: function( filter, force ){

        if( force || !Folder.bayKey[ filter ] ){


            Folder.bayKey[ filter ]  =  DataLayer.get('folder', filter, true);

        }

        return $.extend({}, Folder.bayKey[ filter ]);
    },

    refresh: function(){
        /*
        * Get folders and not cached in Storage
        * */
        Folder.allFolders = DataLayer.get('folder', true);

    }
}