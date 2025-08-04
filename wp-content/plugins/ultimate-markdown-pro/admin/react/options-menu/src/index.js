const {render} = wp.element; //we are using wp.element here!
const {createRoot} = wp.element; //we are using wp.element here!
import App from './App';

const domElement = document.getElementById('react-root');
const uiElement = <App/>;

//check if element exists before rendering
if (domElement) {

  /**
   * Use the proper render method depending on the React version (which depends
   * on the WP version)
   */
  if ( createRoot ) {
    createRoot( domElement ).render( uiElement );
  } else {
    render( uiElement, domElement );
  }

}
