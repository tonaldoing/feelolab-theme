/**
 * Orden de las secciones de la home: arrastrar, o flechas para teclado y lectores de pantalla.
 * Guarda la lista en feelolab_home_order y muestra al lado de cada sección si está oculta.
 */
( function ( api, i18n ) {
	'use strict';

	api.controlConstructor[ 'feelolab-sortable' ] = api.Control.extend( {
		ready: function () {
			var control = this;
			var list = control.container.find( '.feelolab-sortable' )[ 0 ];
			var dragging = null;

			function items() {
				return Array.prototype.slice.call( list.querySelectorAll( '.feelolab-sortable__item' ) );
			}

			function save() {
				control.setting.set( items().map( function ( li ) {
					return li.getAttribute( 'data-key' );
				} ).join( ',' ) );
				// El orden de los apartados del panel sigue al de la lista.
				items().forEach( function ( li, i ) {
					var section = api.section( 'feelolab_home_' + li.getAttribute( 'data-key' ) );
					if ( section ) {
						section.priority( 10 + i );
					}
				} );
			}

			function label( li ) {
				return li.querySelector( '.feelolab-sortable__label' ).textContent;
			}

			// Estado "oculta" al lado de cada sección, vivo mientras se prende o apaga.
			items().forEach( function ( li ) {
				var state = li.querySelector( '.feelolab-sortable__state' );
				api( state.getAttribute( 'data-show-setting' ), function ( setting ) {
					function paint() {
						state.textContent = setting.get() ? '' : i18n.hidden;
						li.classList.toggle( 'is-hidden', ! setting.get() );
					}
					setting.bind( paint );
					paint();
				} );
			} );

			list.addEventListener( 'click', function ( e ) {
				var move = e.target.closest( '.feelolab-sortable__move' );
				var edit = e.target.closest( '.feelolab-sortable__edit' );
				if ( edit ) {
					api.section( edit.getAttribute( 'data-section' ) ).focus();
					return;
				}
				if ( ! move ) {
					return;
				}
				var li = move.closest( '.feelolab-sortable__item' );
				var dir = parseInt( move.getAttribute( 'data-dir' ), 10 );
				var sibling = dir < 0 ? li.previousElementSibling : li.nextElementSibling;
				if ( ! sibling ) {
					return;
				}
				if ( dir < 0 ) {
					list.insertBefore( li, sibling );
				} else {
					list.insertBefore( sibling, li );
				}
				move.focus();
				save();
				wp.a11y.speak( i18n.moved.replace( '%1$s', label( li ) ).replace( '%2$d', items().indexOf( li ) + 1 ).replace( '%3$d', items().length ) );
			} );

			list.addEventListener( 'dragstart', function ( e ) {
				dragging = e.target.closest( '.feelolab-sortable__item' );
				if ( dragging ) {
					dragging.classList.add( 'is-dragging' );
					e.dataTransfer.effectAllowed = 'move';
					e.dataTransfer.setData( 'text/plain', dragging.getAttribute( 'data-key' ) );
				}
			} );
			list.addEventListener( 'dragover', function ( e ) {
				var over = e.target.closest( '.feelolab-sortable__item' );
				if ( ! dragging || ! over || over === dragging ) {
					return;
				}
				e.preventDefault();
				var box = over.getBoundingClientRect();
				list.insertBefore( dragging, e.clientY > box.top + box.height / 2 ? over.nextElementSibling : over );
			} );
			list.addEventListener( 'dragend', function () {
				if ( dragging ) {
					dragging.classList.remove( 'is-dragging' );
					dragging = null;
					save();
				}
			} );
		}
	} );
} )( wp.customize, window.feelolabSortable );
