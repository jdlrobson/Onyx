<?php
/**
 * BaseTemplate class for the Onyx skin.
 *
 * @file
 * @ingroup Skins
 */

class OnyxTemplate extends BaseTemplate {

	/* TODO:
	 *
	 * - Personal tools
	 * - Language links
	 * - Search form
	 *
	 * FUTURE EXTENSIONS:
	 *
	 * - Implement dark scheme using CSS media query prefers-color-scheme: dark
	 * - Read Onyx-specific configuration settings from MediaWiki:Onyx.ini (Use WikiPage)
	 * - Read Onyx-specific navigation links from MediaWiki:Onyx-navigation
	 * - Read Onyx-specific toolbox links from MediaWiki:Onyx-toolbox
	 * - Read user-defined Onyx toolbox links from User:USERNAME/Onyx-toolbox
	 * - Support VisualEditor
	*/

	/**
	 * Outputs the entire contents of the page in HTML form.
	 */
	public function execute() : void {
		// TODO: Load config options from MediaWiki:Onyx.ini and, if enabled by the
		//       wiki, from User:CURRENT_USER/Onyx.ini

		// TODO: Gather all additional data required by the unique features of the
		//       Onyx skin (recent changes and page contents sidebar modules,
		//       custom navigation and toolbox, etc) and add it to the data array
		//       so that those features can be constructed in the same manner as
		//       the ones using the standard MediaWiki API

		$config = new Onyx\Config();

		Onyx\ExtraSkinData::extractAndUpdate( $this->data, $config );

		// Initialise HTML string as a empty string
		$html = '';

		// Concatenate auto-generated head element onto HTML string
		$html .= $this->get( 'headelement' );

		// Build banner
		$this->buildBanner( $html );

		// Build page content
		$this->buildPage( $html );

		// Build footer
		$this->buildFooter( $html );

		// Build toolbox
		$this->buildToolbox( $html );

		// Concatenate auto-generated trail onto HTML string
		$html .= $this->getTrail();

		// Print the entire page's HTML code at once
		echo $html;
	}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////////////////////////                              ////////////////////////
////////////////////////            BANNER            ////////////////////////
////////////////////////                              ////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

	/**
	 * Builds HTML code for the banner that appears at the top of each page, and
	 * appends it to the string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildBanner( string &$html ) : void {
		// Open container section for banner
		$html .= Html::openElement( 'section', [ 'id' => 'onyx-banner' ] );

		// Open container div for banner content
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-banner-content',
			'class' => 'onyx-pageAligned' ] );

		// Build banner logo (floats on the left of the div)
		$this->buildBannerLogo( $html );
		
		// Build user options/login button (floats on the right of the div)
		$this->buildPersonalTools( $html );

		// Build the search bar
		$this->buildSearchBar( $html );

		// Close container div for banner content
		$html .= Html::closeElement( 'div' );

		// Close container section for banner
		$html .= Html::closeElement( 'section' );
	}

	/**
	 * Builds HTML code to present a logo for the wiki on the main banner and
	 * appends it to the string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildBannerLogo( string &$html ) : void {
		// Open container div
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-banner-bannerLogo' ] );

		// Open link element
		$html .= Html::openElement( 'a',
			array_merge( [ 'href' => $this->data['nav_urls']['mainpage']['href'] ],
				Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) ) );
		
		// Insert logo image
		$html .= Html::rawElement( 'img', [ 'id' => 'onyx-bannerLogo-image',
			'src' => $this->get( 'logopath' ), 'alt' => $this->get( 'sitename' ) ] );
		
		// Close link element
		$html .= Html::closeElement( 'a' );

		// Close container div
		$html .= Html::closeElement( 'div' );

	}

	/**
	 * Builds HTML code to present the user account-related options to the reader
	 * and appends it to the string passed to it.
	 * 
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildPersonalTools(string &$html) : void {
		
		// Open container div
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-banner-userOptions' ] );

		$html .= Html::openElement( 'div',
			[ 'id' => 'onyx-userOptions-personalTools',
			'class' => 'onyx-dropdown' ] );


		$html .= Html::openElement( 'div', [ 'id' => 'onyx-personalTools-userButton',
			'class' => 'onyx-dropdown-button' ] );

		// TODO: If SocialProfile is installed, display the user's avatar image
		//       here (and maybe hide text? - decide what looks best once
		//       implemented)

		$html .= Html::rawElement('div', [ 'id' => 'onyx-userButton-avatar' ],
			Onyx\Icon::getIcon( 'avatar' )->makeSvg( 28, 28 ) );

		$html .= Html::rawElement( 'span', [ 'id' => 'onyx-userButton-label' ],
			empty( $this->data['username'] ) ? 'Anonymous' : $this->get( 'username' ) );

		$html .= Html::rawElement( 'div', [ 'id' => 'onyx-userButton-icon',
			'class' => 'onyx-dropdown-icon' ],
			Onyx\Icon::getIcon( 'dropdown' )->makeSvg( 14, 14 ) );
		
		$html .= Html::closeElement( 'div' );
		
		$html .= Html::openElement( 'ul', [ 'id' => 'onyx-personalTools-list',
			'class' => 'onyx-dropdown-list' ] );
		
		foreach ( $this->data['personal_urls'] as $key => $item ) {

			switch ( $key ) {
				case 'userpage':
					$item['text'] = 'User page';
					break;
				case 'mytalk':
					$item['text'] = 'User talk';
					break;
				default:
					break;
			}

			$html .= $this->makeListItem( $key, $item );
		}
		
		$html .= Html::closeElement( 'ul' );

		$html .= Html::closeElement( 'div' );

		// Close container div
		$html .= Html::closeElement( 'div' );

	}

	/**
	 * Builds HTML code to present the search form to the user, and appends it to
	 * string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildSearchBar( string &$html ) : void {
		// Open container div
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-banner-search' ] );

		// TODO: Reorganise this function so that the output is more easily formatted

		$html .= Html::openElement( 'div', [ 'class' => 'mw-portlet', 'id' => 'p-search' ] );

		$html .= Html::rawElement( 'form', [ 'action' => $this->get( 'wgScript' ), 'id' => 'searchform' ],
			Html::rawElement( 'div', [ 'id' => 'simpleSearch' ],
				Html::rawElement( 'div', [ 'id' => 'searchInput-container' ],
					$this->makeSearchInput( [
						'id' => 'searchInput'
					] )
				) .
				Html::hidden( 'title', $this->get( 'searchtitle' ) ) .
				$this->makeSearchButton(
					'fulltext',
					[ 'id' => 'mw-searchButton', 'class' => 'searchButton mw-fallbackSearchButton' ]
				) .
				$this->makeSearchButton(
					'go',
					[ 'id' => 'searchButton', 'class' => 'searchButton' ]
				)
			)
		);

		$html .= Html::closeElement( 'div' );

		// Close container div
		$html .= Html::closeElement( 'div' );

	}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////////////////////////                              ////////////////////////
////////////////////////               PAGE           ////////////////////////
////////////////////////                              ////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

	/**
	 * Builds HTML code to present the bulk of the webpage - the actual page
	 * content itself and appends it to the string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildPage( string &$html ) : void {
		// Open container element for page
		$html .= Html::openElement( 'main',
			[ 'id' => 'onyx-page', 'class' => 'onyx-pageAligned mw-body' ] );
		
		// Build the header
		$this->buildHeader( $html );

		// Open container element for page body (i.e. actual content such as the
		// article and the sidebar)
		$html .= Html::openElement( 'section', [ 'id' => 'onyx-page-pageBody',
			'class' => 'onyx-articleContainer' ] );

		// Build the article content
		$this->buildArticle( $html );

		// Build the sidebar
		$this->buildSidebar( $html );

		// Close container element for page body
		$html .= Html::closeElement( 'section' );

		// Close container element for page
		$html .= Html::closeElement( 'main' );
	}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////////////////////////                              ////////////////////////
////////////////////////           HEADER             ////////////////////////
////////////////////////                              ////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

	/**
	 * Builds HTML code to create the page's header, and appends it to the
	 * string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildHeader( string &$html ) : void {
		// Open container element for header
		$html .= Html::openElement( 'header', [ 'id' => 'onyx-page-header' ] );

		// Build wiki header
		$this->buildWikiHeader( $html );

		// Build article header
		$this->buildArticleHeader( $html );

		// Close container element
		$html .= Html::closeElement( 'header' );
	}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////////////////////////                              ////////////////////////
////////////////////////           WIKI HEADER        ////////////////////////
////////////////////////                              ////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

	/**
	 * Builds HTML code to present global header for wiki pages, containing
	 * content such as the logo, navigation links and wiki name/tagline, and then
	 * appends it to the string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildWikiHeader( string &$html ) : void {
		// Open container div for wiki header
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-header-wikiHeader' ] );

		// Build the header logo
		$this->buildHeaderLogo( $html );

		// Build the tagline heading
		$this->buildTagline( $html );

		// Build the global navigation options
		$this->buildGlobalNav( $html );

		// Close container div
		$html .= Html::closeElement( 'div' );
	}

	/**
	 * Builds HTML code for the wiki's logo in the header, and appends it to the
	 * string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildHeaderLogo( string &$html ) : void {
		// Open container div for logo
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-wikiHeader-headerLogo' ] );
		
		// Open link element
		$html .= Html::openElement( 'a',
			array_merge( [ 'href' => $this->data['nav_urls']['mainpage']['href']],
				Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) ) );
		
		// Insert logo image
		$html .= Html::rawElement( 'img', [
			'id' => 'onyx-headerLogo-image',
			'src' => $this->get( 'logopath' ),
			'alt' => $this->get( 'sitename' )
		] );
		
		// Close link element
		$html .= Html::closeElement( 'a' );

		// Close container div
		$html .= Html::closeElement( 'div' );
	}

	/**
	 * Builds HTML code to display the tagline (or alternatively wiki name) at
	 * top of the header, and appends it to the string that it is passed.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildTagline( string &$html ) : void {
		// Open container div for tagline
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-wikiHeader-tagline' ] );

		// Create heading element containing the tagline, or alternatively the wiki
		// name if no tagline is available
		$html .= Html::rawElement( 'h1', [ 'id' => 'onyx' ],
			empty( $this->data['tagline'] )
			? $this->data['sitename']
			: $this->data['tagline'] );
		
		// Close container div
		$html .= Html::closeElement( 'div' );
	}

	/**
	 * Builds HTML code to display the tagline (or alternatively wiki name) at
	 * top of the header, and appends it to the string that it is passed.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildGlobalNav( string &$html ) : void {
		// Open container element for navigation links
		$html .= Html::openElement( 'nav', [ 'id' => 'onyx-wikiHeader-navigation' ] );

		// Open container element for list
		$html .= Html::openElement( 'ul', [ 'id' => 'onyx-navigation-list' ] );

		// Unset the search, toolbox and languages options from the sidebar array,
		// so that only the navigation will be displayed
		unset( $this->data['sidebar']['SEARCH'] );
		unset( $this->data['sidebar']['TOOLBOX'] );
		unset( $this->data['sidebar']['LANGUAGES'] );

		foreach ( $this->getSidebar() as $boxName => $box ) { 
			// In some instances, getSidebar() will include the toolbox even when
			// data['sidebar']['TOOLBOX'] is unset, so skip any boxNames that don't
			// equal 'navigation'
			if ( $boxName !== 'navigation' ) {
				continue;
			}
			
			if ( is_array( $box['content'] ) ) {
				foreach ( $box['content'] as $key => $item ) {
					$html .= $this->makeListItem( $key, $item );
				}
			} else {
				$html .= Html::rawElement( 'li', [], $box['content'] );
			}
		} 

		// Close container element for link list
		$html .= Html::closeElement( 'ul' );

		// Close container element for global nav
		$html .= Html::closeElement( 'nav' );
	}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////////////////////////                              ////////////////////////
////////////////////////        ARTICLE HEADER        ////////////////////////
////////////////////////                              ////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

	/**
	 * Builds HTML code to display the article section of the header dedicated to
	 * article-specific information and options, such as the title and content
	 * action buttons, and then appends it to the string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildArticleHeader( string &$html ) : void {
		// Open container div for article header
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-header-articleHeader' ] );

		// Open container div for article action options
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-articleHeader-actions' ] );

		// Build content action buttons
		$this->buildActionButtons( $html );

		// Close container div for action options
		$html .= Html::closeElement( 'div' );

		// Open h1 element for article title
		$html .= Html::openElement( 'h1',
			[ 'id' => 'onyx-articleHeader-title' ] );
		
		// Insert page status indicators (these will float right inside the h1
		// element)
		$html .= $this->getIndicators();

		// Insert article title
		$html .= Html::rawElement( 'span',
			[ 'id' => 'onyx-title-text' ],
			$this->get( 'title' ) );
		
		// Close h1 element
		$html .= Html::closeElement( 'h1' );

		// If it exists, insert the subtitle
		if ( !empty( $this->data['subtitle'] ) ) {
			$html .= Html::rawElement( 'div',
				[ 'id' => 'onyx-articleHeader-subtitle' ],
				$this->get( 'subtitle' ) );
		}

		// If it exists, insert the article undelete message
		if ( !empty( $this->data['subtitle'] ) ) {
			$html .= Html::rawElement('div',
				[ 'id' => 'onyx-articleHeader-undelete' ],
				$this->get( 'undelete' ) );
		}

		// Close container div for article header
		$html .= Html::closeElement( 'div' );

	}

	// TODO: Clean up the following three functions (buildActionButtons,
	//       buildActionButton and buildActionDropdown).

	/**
	 * Builds HTML code to present the content action options to the user, and
	 * appends it to the string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildActionButtons( string &$html ) : void {
		$skin = $this->getSkin();
		$edit = null;
		$talk = null;
		$sidebar = [
			'id' => 'onyx-actions-toggleSidebar',
			'class' => 'onyx-button onyx-button-secondary onyx-button-action',
			'imgType' => 'svg',
			'imgSrc' => 'sidebar',
			'text' => 'Sidebar',
			'title' => 'Expand or collapse the sidebar'
		];
		$dropdown = [];

		// Sort through the flat content actions array provided by the API, and
		// extract, discard and modify what is necessary
		foreach ( $this->data['content_actions'] as $key => $tab ) {
			// Discard any content actions of the form 'nstab-***'. These correspond
			// to the options to view the page itself, which have no need to be
			// presented to the user when they are already on the page
			if ( substr( $key, 0, 6 ) === 'nstab-' ) {
				continue;
			}

			switch ( $key ) {
				// If the action is edit or view source, assign the tab array to the
				// edit variable, and specify the path to the image to use as the
				// button's icon
				case 'edit':
					$edit = $tab;
					$edit['imgType'] = 'svg';
					$edit['imgSrc'] = 'edit';
					break;
				case 'viewsource':
					$edit = $tab;
					$edit['imgType'] = 'svg';
					$edit['imgSrc'] = 'view';
					break;
				// If the action is talk, assign the tab array to the talk variable and
				// specify the path to the button icon
				case 'talk':
					$talk = $tab;
					$talk['text'] = 'Talk';
					$talk['imgType'] = 'svg';
					$talk['imgSrc'] = 'talk';
					break;
				// If the action is add section, then replace the tooltip (which is, by
				// default, just a '+') with 'Add new section', a more appropriate
				// message for a drop-down list format and then DELIBERATELY fall
				// through to the default case
				case 'addsection':
					$tab['text'] = $skin->msg( 'onyx-actions-addsection' )->escaped();
				// Finally, if the content action is none of the above, add it to the
				// growing array of miscellaneous content actions to be displayed in a
				// drop-down list beneath the edit/view soure button
				default:
					$dropdown[$key] = $tab;
					break;
			}
		}

		// Add Onyx-specific IDs and classes to the edit and talk buttons
		if ( !empty( $edit ) ) {
			$edit['id'] .= ' onyx-actions-edit';
			$edit['class'] .= ' onyx-button onyx-button-primary onyx-button-action';
		}
		if ( !empty( $talk ) ) {
			$talk['id'] .= ' onyx-actions-talk';
			$talk['class'] .= ' onyx-button onyx-button-secondary onyx-button-action';
		}

		// If the edit content action is available, display it as a button
		if ( $edit !== null ) {
			$this->buildActionButton( $html, $edit );
		}

		// If there are one or more miscellaneous content actions available,
		// display them as a drop-down list following the edit button
		if ( sizeof( $dropdown ) > 0 ) {
			$this->buildActionDropdown( $html, $dropdown );
		}

		// If the talk content action is available, display it as a button
		if ( $talk !== null ) {
			$this->buildActionButton( $html, $talk );
		}

		// Finally, display the sidebar toggle button, which will always be
		// available
		$this->buildActionButton( $html, $sidebar );
	}

	/**
	 * Builds HTML code to for an individual content action button, and appends
	 * it to the string passed
	 *
	 * @param $html string The string onto which the HTML should be appended
	 * @param $info array An array with the necessary info to build the button
	 */
	protected function buildActionButton( string &$html, array $info ) : void {
		// If the button links to another page, surround it in an <a> element that
		// links there
		if ( !empty( $info['href'] ) ) {
			$html .= Html::openElement( 'a', [ 'href' => $info['href'],
				'title' => $info['title'] ?? '' ] );
		}

		// Open a <div> for the button
		$html .= Html::openElement( 'div', [ 'id' => $info['id'],
				'class' => $info['class'] ] );

		if ( isset( $info['imgSrc'] ) ) {
			// If the button is to have an icon, display the icon in the format
			// corresponding to the given image type
			switch ( $info['imgType'] ) {
				case 'svg':
					$icon = Onyx\Icon::getIcon( $info['imgSrc'] );
					if ( !isset($icon) ) {
						break;
					}
					$html .= $icon->makeSvg( 28, 28, [ 'class' => 'onyx-button-icon' ] );
					break;
				default:
					$stylePath = $this->getSkin()->getConfig()->get( 'StylePath' );
					$html .= Html::rawElement( 'img', [ 'src' => $stylePath
						. '/Onyx/resources/icons/' . $info['imgSrc'] ] );
					break;
			}
		}

		// Place the button text in a <span> element
		$html .= Html::rawElement( 'span', [ 'class' => 'onyx-button-text' ],
			$info['text'] );

		// Close the main button <div> element
		$html .= Html::closeElement( 'div' );

		// If necessary, close the <a> element surrounding the button too
		if ( isset( $info['href'] ) ) {
			$html .= Html::closeElement( 'a' );
		}
	}

	/**
	 * Builds HTML code to for a drop-down list of selectable content actions,
	 * and appends it to a given string
	 *
	 * @param $html string The string onto which the HTML should be appended
	 * @param $info array An array of items which should be placed in the list
	 */
	protected function buildActionDropdown( string &$html, array $items) : void {
		// Open a <div> element to contain the entire drop-down
		$html .= Html::openElement( 'div', [
			'class' => 'onyx-dropdown',
			'id' => 'onyx-actions-actionsList'
		] );

		// Open a div for a button that will display the list when hovered over
		// (this is achieved via CSS styling of the onyx-dropdown,
		// onyx-dropdown-button, onyx-dropdown-icon and onyx-dropdown-list classes)
		$html .= Html::openElement( 'div', [
			'class' => 'onyx-button onyx-button-primary onyx-button-action '
				. 'onyx-dropdown-button',
			'id' => 'onyx-actionsList-button'
		] );
		
		// Insert the dropdown icon
		$html .= Html::rawElement( 'div', [
			'id' => 'onyx-actionsList-dropdownIcon',
			'class' => 'onyx-dropdown-icon'
			], Onyx\Icon::getIcon( 'dropdown' )->makeSvg( 14, 14 ) );
		
			// Close the button div
		$html .= Html::closeElement( 'div' );

		// Open an <ul> element to contain the list itself
		$html .= Html::openElement( 'ul', [
			'class' => 'onyx-dropdown-list',
			'id' => 'onyx-actionsList-list'
		] );

		// Step through the array and use the makeListItem to convert each of the
		// items into a properly formatted HTML <li> element
		foreach ( $items as $key => $value ) {
			$html .= $this->makeListItem( $key, $value );
		}

		// Close the <ul> list container
		$html .= Html::closeElement( 'ul' );

		// Close the <div> container
		$html .= Html::closeElement( 'div' );
	}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////////////////////////                              ////////////////////////
////////////////////////        PAGE SIDEBAR          ////////////////////////
////////////////////////                              ////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

	/**
	 * Builds HTML code for the sidebar and its content, and appends it to the
	 * string that is passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildSidebar( string &$html ) : void {
		// Open container element for sidebar
		$html .= Html::openElement( 'aside', [
			'id' => 'onyx-pageBody-sidebar',
			'class' => 'onyx-sidebarAligned'
		] );
		
		// Open container div for static sidebar modules
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-sidebar-staticModules' ] );

		// Build the static custom sidebar module
		$this->buildStaticCustomModule( $html );

		// Build the recent changes module
		$this->buildRecentChangesModule( $html );

		// Close container div for static modules
		$html .= Html::closeElement( 'div' );

		// Open container div for sticky sidebar modules
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-sidebar-stickyModules' ] );

		// Build the article contents navigation module
		$this->buildPageContentsModule( $html );

		// Build the sticky custom module
		$this->buildStickyCustomModule( $html );

		// Close container div for sticky modules
		$html .= Html::closeElement( 'div' );

		// Close container element for sidebar
		$html .= Html::closeElement( 'aside' );
	}

	/**
	 * Builds HTML code to display the wiki's static custom Onyx sidebar module,
	 * and appends it to the string it is passed.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildStaticCustomModule( string &$html ) : void {
		global $wgOut;
		
		// Open container div for module
		$html .= Html::openElement( 'div', [
			'id' => 'onyx-staticModules-custom',
			'class' => 'onyx-sidebarModule onyx-sidebarModule-static'
		] );
		
		// Have the MediaWiki parser output the Template:Onyx/Sidebar/Static page
		// and insert it into the page
		$html .= $wgOut->parseAsContent( '{{Onyx/Sidebar/Static}}' );

		// Close container div for module
		$html .= Html::closeElement( 'div' );
	}

	/**
	 * Builds HTML code to display a sidebar module showing recent changes and
	 * wiki activity, then appends it to the string it is passed.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildRecentChangesModule( string &$html ) : void {
		// Open container div for module
		$html .= Html::openElement('div', [
			'id' => 'onyx-staticModules-recentChanges',
			'class' => 'onyx-sidebarModule onyx-sidebarModule-static'
		] );
		
		// Insert module title
		$html .= Html::rawElement( 'h2', [
			'id' => 'onyx-recentChanges-heading',
			'class' => 'onyx-sidebarHeading onyx-sidebarHeading-static'
			], 'Recent Changes');
		
		// Open container div for module content
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-recentChanges-content' ] );
		
		// Open unordered list
		$html .= Html::openElement( 'ul' );

		// Get the current time
		$currentTime = DateTime::createFromFormat( 'YmdHis', wfTimestampNow() );

		// Loop through all the recent changes provided by Onyx\ExtraSkinData
		foreach ( $this->data['onyx_recentChanges'] as $recentChange ) {

			// Get the time the edit was made
			$time = DateTime::createFromFormat('YmdHis', $recentChange['timestamp']);

			// Get a string representing the time difference
			$timeDiff = self::getDateTimeDiffString( $currentTime->diff( $time ) ) . ' ago';

			// Get the title of the page that was edited
			$page = Title::newFromText( $recentChange['title'], $recentChange['namespace'] );

			// Get the title of the userpage of the user who edited it
			$user = Title::newFromText( $recentChange['user'], NS_USER );

			// Open list item for recent change
			$html .= Html::openElement( 'li' );

			// Create a link to the edited page
			$html .= Html::openElement( 'a', [ 'href' => $page->getInternalURL() ] );
			$html .= Html::rawElement( 'span', [], $page->getFullText() );
			$html .= Html::closeElement( 'a' );

			// Create a link to the user who edited it
			$html .= Html::openElement( 'a', [ 'href' => $user->getInternalURL() ] );
			$html .= Html::rawElement( 'span', [], $user->getText() );
			$html .= Html::closeElement( 'a' );

			// Display how long ago it was edited
			$html .= Html::rawElement( 'span', [], $timeDiff );

			// Close the list item
			$html .= Html::closeElement( 'li' );
		}

		// Close unordered list
		$html .= Html::closeElement( 'ul' );

		// Close container div for module content
		$html .= Html::closeElement( 'div' );

		// Close container div for module
		$html .= Html::closeElement( 'div' );
	}

	/**
	 * Generates a textual representation of a DateInterval, ignoring all but the
	 * largest denomination of time
	 *
	 * @param $interval DateInterval The interval to generate a representation of
	 */
	protected static function getDateTimeDiffString( DateInterval $interval ) {
		if ( $interval->y > 0 ) {
			return $interval->format( '%y years' );
		} elseif ( $interval->m > 0 ) {
			return $interval->format( '%m months' );
		} elseif ( $interval->d > 0 ) {
			return $interval->format( '%d days' );
		} elseif ( $interval->h > 0 ) {
			return $interval->format( '%h hours' );
		} elseif ( $interval->i > 0 ) {
			return $interval->format( '%i minutes' );
		} else {
			return $interval->format( '%s seconds' );
		}
	}

	/**
	 * Retrieves the article contents navigation list from the article content
	 * and builds HTML code to display it to the user as a sidebar module, then
	 * appends this HTML to the string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildPageContentsModule( string &$html ) : void {
		// If, for whatever reason, Onyx\ExtraSkinData has not provided the page
		// contents (due to config settings or lack of enough headings),
		// do nothing
		if ( empty( $this->data['onyx_pageContents'] ) ) {
			return;
		}

		// Also do nothing if we're on NS_SPECIAL (or oter virtual namespaces, though
		// de facto only NS_SPECIAL pages are exposed in the UI)
		if ($this->getSkin()->getTitle()->getNamespace() < 0) {
			return;
		}

		// Open container div for module
		$html .= Html::openElement( 'div', [
			'id' => 'onyx-stickyModules-pageContents',
			'class' => 'onyx-sidebarModule onyx-sidebarModule-sticky'
		] );
		
		// Insert module title
		$html .= Html::rawElement( 'h2', [
			'id' => 'onyx-pageContents-heading',
			'class' => 'onyx-sidebarHeading onyx-sidebarHeading-sticky'
			], 'Contents' );
		
		// Open container div for module content
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-pageContents-content' ] );
		
		$this->buildPageContentsModuleList( $html, $this->data['onyx_pageContents'] );

		// Close container div for module content
		$html .= Html::closeElement( 'div' );

		// Close container div for module
		$html .= Html::closeElement( 'div' );
	}

	/**
	 * Builds the list that will be displayed in the page contents module.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 * @param $headings array The list of page headings generated by Onyx\ExtraSkinData
	 */
	protected function buildPageContentsModuleList( string &$html,
			array $headings ) : void {
		// Open the unordered list element that will contain the list
		$html .= Html::openElement( 'ul', [ 'id' => 'onyx-pageContents-list' ] );

		// Loop through the list of headings provided
		foreach ($headings as $heading) {
			// Open a list item for the heading
			$html .= Html::openElement( 'li', [ 'id' => 'onyx-pageContents-listItem' ] );

			// Open a link that points to the heading's location
			$html .= Html::openElement( 'a', [ 'href' => '#'.$heading['href-id'] ] );

			// Display the heading's prefix (e.g. '2.3.1')
			$html .= Html::element( 'span', [
				'id' => 'onyx-pageContents-itemPrefix'
				], $heading['prefix'] );

			// Display the heading's title
			$html .= Html::element( 'span', [
				'id' => 'onyx-pageContents-itemLabel'
				], $heading['name'] );
			
			// Close the link
			$html .= Html::closeElement('a');

			// If the heading has any subheadings, then recursively build the list
			// for those too
			if ( !empty( $heading['children'] ) ) {
				$this->buildPageContentsModuleList( $html, $heading['children'] );
			}

			// Close the list item for the heading
			$html .= Html::closeElement( 'li' );
		}

		// Close the list
		$html .= Html::closeElement( 'ul' );
	}

	/**
	 * Builds HTML code to display the wiki's sticky custom Onyx sidebar module,
	 * and appends it to the string it is passed.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildStickyCustomModule( string &$html ) : void {
		global $wgOut;
		
		// Open container div for module
		$html .= Html::openElement( 'div', [
			'id' => 'onyx-stickyModules-custom',
			'class' => 'onyx-sidebarModule onyx-sidebarModule-sticky'
		] );
		
		// Have the MediaWiki parser output the Template:Onyx/Sidebar/Sticky page
		// and insert it into the page
		$html .= $wgOut->parseAsContent( '{{Onyx/Sidebar/Sticky}}' );

		// Close container div for module
		$html .= Html::closeElement( 'div' );
	}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////////////////////////                              ////////////////////////
////////////////////////        PAGE CONTENT          ////////////////////////
////////////////////////                              ////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

	/**
	 * Builds HTML code to display the content of the page itself and appends it
	 * to the string it is given.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildArticle( string &$html ) : void {
		// Open container element for article
		$html .= Html::openElement( 'article', [ 'id' => 'onyx-pageBody-content' ] );

		// If it exists, display the site notice at the top of the article
		if ( !empty($this->data['sitenotice'] ) ) {
			$html .= Html::openElement('div', ['id' => 'onyx-content-siteNotice']);

			// Display the site notice close button
			$html .= Html::rawElement( 'div', [
				'class' => 'onyx-button onyx-button-primary',
				'id' => 'onyx-siteNotice-closeButton'
				], Onyx\Icon::getIcon( 'close' )->makeSvg( 14, 14,
					[ 'id' => 'onyx-siteNotice-closeIcon' ] )
			);
			
			$html .= $this->get( 'sitenotice' );

			$html .= Html::closeElement( 'div' );
		}

		// Insert the content of the article itself
		$html .= $this->get( 'bodytext' );

		// If appropriate, insert the category links at the bottom of the page
		if ( !empty( $this->data['catlinks'] ) ) {
			$html .= Html::rawElement( 'span', [
				'id' => 'onyx-content-categories'
				], $this->get( 'catlinks' )
			);
		}

		// If there is any additional data or content to show, insert it now
		if ( !empty( $this->data['dataAfterContent'] ) ) {
			$html .= Html::rawElement( 'span', [
				'id' => 'onyx-content-additionalContent'
				], $this->get( 'dataAfterContent' )
			);
		}

		// Close container element for article
		$html .= Html::closeElement( 'article' );
	}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////////////////////////                              ////////////////////////
////////////////////////        FOOTER                ////////////////////////
////////////////////////                              ////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

	/**
	 * Builds HTML code for the page foooter, and appends it to the string passed
	 * to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildFooter( string &$html ) : void {
		// Open container element for footer
		$html .= Html::openElement( 'footer', [ 'id' => 'onyx-footer' ] );
		
		// Open container element for footer content
		$html .= Html::openElement( 'div', [
			'id' => 'onyx-footer-footerContent',
			'class' => 'onyx-pageAligned onyx-articleContainer'
		] );

		// Build the footer links
		$this->buildFooterLinks( $html );

		// Build the footer icons
		$this->buildFooterIcons( $html );
		
		// Close container element for footer content
		$html .= Html::closeElement( 'div' );

		// Close container element for footer
		$html .= Html::closeElement( 'footer' );
	}

	/**
	 * Builds HTML code to display the footer icons, and appends it to the string
	 * that is passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildFooterIcons( string &$html ) : void {
		// Open container div for icons
		$html .= Html::openElement( 'div', [
			'id' => 'onyx-footerContent-footerIcons',
			'class' => 'onyx-sidebarAligned'
		] );
		
		// Open unordered list element for icon list
		$html .= Html::openElement( 'ul', [ 'id' => 'onyx-footerIcons-list' ] );

		// TODO: Split blocks of footer icons appropriately (i.e. make a new list
		//       for each iteration of the outer loop)

		// Loop through each footer icon and generate a list item element
		// which contains the icon to display
		foreach ( $this->getFooterIcons( 'icononly' ) as $blockName => $footerIcons ) {

			$html .= Html::openElement( 'li', [
				'class' => 'onyx-footerIcons-listItem'
			] );
			
			foreach ( $footerIcons as $icon ) {
				$html .= $this->getSkin()->makeFooterIcon( $icon );
			}

			$html .= Html::closeElement( 'li' );
		}

		// Close unordered list element
		$html .= Html::closeElement( 'ul' );

		// Close container div
		$html .= Html::closeElement( 'div' );
	}

	/**
	 * Builds HTML code to display the footer links, and appends it to the string
	 * that is passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildFooterLinks( string &$html ) : void {
		// Open container div for footer links
		$html .= Html::openElement( 'div', [
			'id' => 'onyx-footerContent-footerLinks'
		] );
		
		// Open unordered list element for link list
		$html .= Html::openElement('ul', ['id' => 'onyx-footerLinks-list' ] );

		// TODO: Migrate to using getFooterLinks() instead of
		//       getFooterLinks('flat'), so that footer links can be divided into
		//       categories.

		// Loop through each footer link and generate a list item element
		// which contains the link text
		foreach ( $this->getFooterLinks( 'flat' ) as $link ) {
			$html .= Html::rawElement( 'li', [
				'class' => 'onyx-footerLinks-listItem'
				], $this->get( $link )
			);
		}

		// Close unordered list element
		$html .= Html::closeElement( 'ul' );

		// Close container div
		$html .= Html::closeElement( 'div' );
	}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
////////////////////////                              ////////////////////////
////////////////////////            TOOLBOX           ////////////////////////
////////////////////////                              ////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

	/**
	 * Builds HTML code for the toolbox that is displayed at the bottom of the
	 * page, and appends it to the string of HTML that is it passed.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildToolbox( string &$html ) : void {
		// Open container element for toolbox
		$html .= Html::openElement( 'section', [ 'id' => 'onyx-toolbox' ] );

		// Open container div for toolbox content
		$html .= Html::openElement( 'div', [ 'id' => 'onyx-toolbox-tools' ] );

		// Begin unordered list to contain tool links
		$html .= Html::openElement( 'ul', [ 'id' => 'onyx-tools-list' ] );

		// Make a list item for each of the tool links
		foreach ( $this->getToolbox() as $key => $toolboxItem ) {
			$html .= $this->makeListItem( $key, $toolboxItem );
		}

		// Avoid PHP 7.1 warnings
		$skin = $this;

		Hooks::run( 'SkinTemplateToolboxEnd', [ &$skin, true ] );

		// End unordered list
		$html .= Html::closeElement( 'ul' );

		// Close container div
		$html .= Html::closeElement( 'div' );

		// Close container element
		$html .= Html::closeElement( 'section' );
	}

}
