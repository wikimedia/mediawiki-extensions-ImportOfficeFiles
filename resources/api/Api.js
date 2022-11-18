officeimport.api.Api = function () {
};

OO.initClass( officeimport.api.Api );

officeimport.api.Api.prototype.getPagesStructure = function ( uploadId ) {
	return this.get( 'file_structure/{0}'.format( uploadId ) );
};

officeimport.api.Api.prototype.get = function ( path, data, timeout ) {
	data = data || {};
	return this.ajax( path, data, 'GET', timeout );
};

officeimport.api.Api.prototype.post = function ( path, params, timeout ) {
	params = params || {};
	return this.ajax( path, params, 'POST', timeout );
};

officeimport.api.Api.prototype.ajax = function ( path, data, method, timeout ) {
	data = data || {};
	const dfd = $.Deferred();

	const config = {
		method: method,
		url: this.makeUrl( path ),
		data: data,
		contentType: 'application/json',
		dataType: 'json'
	};

	if ( typeof timeout !== 'undefined' ) {
		config.timeout = timeout;
	}

	$.ajax( config ).done( function ( response ) {
		dfd.resolve( response );
	} ).fail( function ( xhr, type, status ) {
		// eslint-disable-next-line no-console
		console.dir( status );
		dfd.reject();
	} );

	return dfd.promise();
};

officeimport.api.Api.prototype.makeUrl = function ( path ) {
	if ( path.charAt( 0 ) === '/' ) {
		path = path.slice( 1 );
	}

	return mw.util.wikiScript( 'rest' ) + '/officeimport/{0}'.format( path );
};

officeimport.api.Api.prototype.uploadFile = function ( file ) {
	const dfd = $.Deferred();

	const formData = new FormData();
	formData.append( 'file', file[ 0 ], file[ 0 ].name );

	$.ajax( {
		method: 'POST',
		url: this.makeUrl( 'file_storage' ),
		data: formData,
		contentType: false,
		processData: false
	} ).done( function ( response ) {
		if ( response.success === false ) {
			dfd.reject();
			return;
		}
		dfd.resolve( response );
	} ).fail( function ( xhr, type, status ) {
		// eslint-disable-next-line no-console
		console.dir( xhr );
		// eslint-disable-next-line no-console
		console.dir( type );
		// eslint-disable-next-line no-console
		console.dir( status );
		dfd.reject( status, xhr );
	} );

	return dfd.promise();
};

officeimport.api.Api.prototype.doAnalyze = function ( uploadId, filename, data ) {
	return this.get( 'file_analyze/{0}/{1}'.format( uploadId, encodeURIComponent( filename ) ),
		JSON.stringify( { config: data } ),
		300 * 1000
	);
};

officeimport.api.Api.prototype.getContent = function ( uploadId, title ) {
	return this.get( 'file_content/{0}'.format( uploadId ),
		JSON.stringify( { title: title } )
	);
};

officeimport.api.Api.prototype.importImages = function ( uploadId ) {
	return this.get( 'file_import/images/{0}'.format( uploadId ), {}, 300 * 1000 );
};

officeimport.api.Api.prototype.importPages = function ( uploadId ) {
	return this.get( 'file_import/pages/{0}'.format( uploadId ), {}, 300 * 1000 );
};

officeimport.api.Api.prototype.removeTemporaryFiles = function ( uploadId ) {
	return this.get( 'file_import/remove_temporary_files/{0}'.format( uploadId ) );
};
