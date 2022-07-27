jQuery( document ).ready( function($) {
    $(".lioncub-toggle").on( "change", function() {
        let id = $( this ).attr( "id" )
        $( "." + id ).hide();
        if ( $( this ).is( ":checked" ) ) {
            $( "." + id ).show();
        }
    }).change();
});

function AddLionCubHeaderField( id, name, value, tag ) {
    if( ! document.getElementById && document.createElement ) {
        return;
    }
    let holder = document.getElementById( id );
    let input = document.createElement( "input" );
    input.name = name;
    input.type = 'text';
    input.value = value;
    input.className = 'large-text';
    if ( tag.length > 0 ) {
        let thetag = document.createElement( tag );
        thetag.appendChild( input );
        holder.appendChild( thetag );
    } else {
        holder.appendChild( input );
    }
}

function AddLionCubPropField( id, key, name, value, tag ) {
    if( ! document.getElementById && document.createElement ) {
        return;
    }
    let holder = document.getElementById( id );
    let input = [];
    let label = [];
    let number = holder.getElementsByClassName( 'lioncub-props' );
    for (let i = 0; i < 4; i++) {
        input[i] = document.createElement("input");
        input[i].name = name;
        if ( i < 2 ) {
            input[i].type = 'text';
            input[i].value = value;
        } else {
            input[i].id = 'lioncub-properties-expose' + i;
            input[i].type = 'checkbox';
            label[i] = document.createElement("label");
            if ( i === 2 ) {
                label[i].innerHTML = "Expose property?";
                input[i].name = 'lioncub[' + key + '][properties_expose][]';
            }
            if ( i === 3 ) {
                label[i].innerHTML = "Enforce property?";
                input[i].name = 'lioncub[' + key + '][properties_enforce][]';
            }
            label[i].setAttribute( "for", "lioncub-properties-expose" + i );
        }
    }
    if ( tag.length > 0 ) {

        let thetag = document.createElement( tag );
        let spacer = document.createTextNode( "\u00A0\u00A0\u00A0" );
        let spacer2 = document.createTextNode( "\u00A0\u00A0\u00A0" );
        let spacer3 = document.createTextNode( "\u00A0\u00A0\u00A0" );

        thetag.appendChild( input[0] );
        thetag.appendChild( spacer );
        thetag.appendChild( input[1] );
        thetag.appendChild( spacer2 );
        thetag.appendChild( input[2] );
        thetag.appendChild( label[2] );
        thetag.appendChild( spacer3 );
        thetag.appendChild( input[3] );
        thetag.appendChild( label[3] );

        holder.appendChild( thetag );

    } else {
        holder.appendChild( input[0] );
        holder.appendChild( input[1] );
        holder.appendChild( input[2] );
        holder.appendChild( input[3] );
    }
}