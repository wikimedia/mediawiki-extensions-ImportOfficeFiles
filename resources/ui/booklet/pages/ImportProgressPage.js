officeimport.ui.ImportProgressPage = function ( name, cfg ) {
	officeimport.ui.ImportProgressPage.parent.call( this, name, cfg );

	this.progressBar = new OO.ui.ProgressBarWidget( {
		progress: 0
	} );

	this.$status = $( '<span>' ).addClass( 'import-progress-status' );

	this.fieldLayout = new OO.ui.FieldLayout( this.progressBar, {
		label: mw.message( 'importofficefiles-ui-import-in-progress' ).plain(),
		align: 'top',
		classes: [ 'import-progress-layout' ]
	} );

	this.currentStep = 0;
	this.steps = [
		'import-images-step',
		'import-pages-step',
		'remove-files-step'
	];

	this.progressBarValue = 0;
	this.progressBarStep = 33;

	this.updateProgressUI();

	this.$element.append( this.$status );
	this.$element.append( this.fieldLayout.$element );
};

OO.inheritClass( officeimport.ui.ImportProgressPage, OO.ui.PageLayout );

/**
 * @param {Array} pages
 * @param {string} title
 * @private
 */
officeimport.ui.ImportProgressPage.prototype.onImportDone = function ( pages, title ) {
	const namespaceIds = mw.config.get( 'wgNamespaceIds' );
	const namespaceKey = namespaceIds.mediawiki;
	const params = { prefix: 'Page collection' };

	mw.hook( 'importOffice.collectionPrefix' ).fire( params );

	const pageTitle = params.prefix + '/' + title;
	this.targetTitle = mw.Title.newFromText( pageTitle, namespaceKey );

	this.setImportDoneUI();

	const linklist = this.getPageList( pages );
	this.exportList( linklist );
};

/**
 * Starts import process.
 *
 * This process is split to several steps, which are executed on background one by one.
 * To check import status and proceed to the next step (if available) see:
 * {@link officeimport.ui.ImportProgressPage.checkImportStatus}
 *
 * @param {string} uploadId ID of workspace directory, where file was uploaded to
 * @param {string} fileName Uploaded filename
 */
officeimport.ui.ImportProgressPage.prototype.startImport = function ( uploadId ) {
	mw.loader.using( [ 'ext.importofficefiles.api' ], function () {
		const api = new officeimport.api.Api();
		api.importImages( uploadId ).done( function ( response ) {
			if ( response.success ) {
				this.progressBarValue += this.progressBarStep;
				this.currentStep++;

				this.updateProgressUI();

				api.importPages( uploadId ).done( function ( importPagesResponse ) {
					if ( importPagesResponse.success ) {
						this.progressBarValue += this.progressBarStep;
						this.currentStep++;

						this.updateProgressUI();

						api.removeTemporaryFiles( uploadId )
							.done( function ( removeTempFilesResponse ) {
								if ( removeTempFilesResponse.success ) {
									this.emit( 'importDone' );
								}
							}.bind( this ) ).fail( function ( error ) {
								this.emit( 'importFailed', 'Removing of temporary files failed', error );
							}.bind( this ) );
					} else {
						this.emit( 'importFailed', 'Import pages failed', importPagesResponse );
					}
				}.bind( this ) ).fail( function ( error ) {
					this.emit( 'importFailed', 'Import pages failed to start', error );
				}.bind( this ) );
			} else {
				// Import process failed to start
				this.emit( 'importFailed', 'Import images failed', response );
			}
		}.bind( this ) ).fail( function ( error ) {
			// Probably API is unreachable currently, or there is some fatal
			this.emit( 'importFailed', 'Import images failed to start', error );
		}.bind( this ) );
	}.bind( this ) );
};

/**
 * @private
 */
officeimport.ui.ImportProgressPage.prototype.updateProgressUI = function () {
	const statusMessageKey = 'importofficefiles-ui-progress-' + this.steps[ this.currentStep ];
	// The following messages are used here
	// * importofficefiles-ui-progress-import-images-step
	// * importofficefiles-ui-progress-import-pages-step
	// * importofficefiles-ui-progress-remove-files-step
	this.$status.text( mw.message( statusMessageKey ).text() );

	this.progressBar.setProgress( this.progressBarValue );
};

officeimport.ui.ImportProgressPage.prototype.setImportDoneUI = function () {
	this.progressBar.setProgress( 100 );
	this.$status.text( '' );
	this.fieldLayout.toggle( false );

	const labelSuccess = new OO.ui.LabelWidget( {
		label: mw.message( 'importofficefiles-ui-import-completed' ).plain()
	} );

	this.$element.append( labelSuccess.$element );

	const $link = $( '<a>' ).attr( 'href', this.targetTitle.getUrl() );
	$link.attr( 'title', this.targetTitle.getPrefixedText() );
	$link.html( this.targetTitle.getPrefixedText() );

	const $linkHtml = $( '<div>' ).addClass( 'import-list' ).append(
		mw.message( 'importofficefiles-ui-dialog-link-import-files-text', $link ).parse()
	);
	this.$element.append( $linkHtml );
};

officeimport.ui.ImportProgressPage.prototype.getPageList = function ( pages ) {
	let formatContent = '';

	for ( let i = 0; i < pages.length; i++ ) {
		let link = '* ';
		link += '[[' + pages[ i ] + ']]\n';
		formatContent += link;
	}

	return formatContent;
};

officeimport.ui.ImportProgressPage.prototype.exportList = function ( linklist ) {
	const me = this;
	const dfd = $.Deferred();
	mw.loader.using( 'mediawiki.api' ).done( function () {
		const api = new mw.Api();
		api.postWithToken( 'csrf', {
			action: 'edit',
			title: me.targetTitle.getPrefixedText(),
			text: linklist
		} ).done( function ( response ) {
			dfd.resolve( response );
		} ).fail( function ( code, err ) {
			dfd.reject( code, err );
		} );
	} );

	return dfd.promise();
};
