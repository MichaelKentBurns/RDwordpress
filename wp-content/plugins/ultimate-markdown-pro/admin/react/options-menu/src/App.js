import InputField from './components/InputField';
import MainHeader from './components/MainHeader';
import Navigation from './components/Navigation';
import SettingsContainer from './components/SettingsContainer';
import DismissibleNotice from './components/DismissibleNotice';
import settings from '../../shared-components/data/settings';
import LoadingScreen from '../../shared-components/LoadingScreen';

const useEffect = wp.element.useState;
const useState = wp.element.useState;
const { __, _x, _n, _nx } = wp.i18n;
import GridiconProductDownloadable from 'gridicons/dist/product-downloadable';
import GridiconReaderFollowConversation from 'gridicons/dist/reader-follow-conversation';
import GridiconInstitution from 'gridicons/dist/institution';

const App = () => {

  //Used by the Navigation component
  const [activePage, setActivePage] = useState(1);
  const [isSearching, setIsSearching] = useState(false);
  const [searchInput, setSearchInput] = useState('');
  const [dataAreLoaded, setDataAreLoaded] = useState(false);

  const [formData, setFormData] = useState(
      getDefinedOptions()
  );

  //Generate an object with all the empty options defined in pages.js
  function getDefinedOptions(){

    let options = {};

    //create a each that iterates over the pages array
    settings.pages.forEach(page => {

        //create a each that iterates over the cards array
        page.cards.forEach(card => {

          //create a each that iterates over the options array
          card.options.forEach(option => {

            //add the option to the options object
            options[option.name] = '';

          });

        });

    });

    return options;

  }

  useEffect(() => {

    /**
     * Initialize the options fields with the data received from the REST API
     * endpoint provided by the plugin.
     */
    wp.apiFetch({
      path: '/ultimate-markdown-pro/v1/read-options',
      method: 'POST'
    }).
        then(data => {

              let options = {};

              //generate an object with all the options
              settings.pages.forEach(page => {
                page.cards.forEach(card => {
                  card.options.forEach(option => {
                    options[option.name] = data[option.name];
                  });
                });
              });

              //set the options in the state
              setFormData(prevFormData => {
                return {
                  ...prevFormData,
                  ...options,
                };
              });

              setDataAreLoaded(true);

            },
        );

  });

  //Used by the Navigation component
  function handleSearchInputChanges(e) {
    setSearchInput(e.target.value);
  }

  //Used by the Navigation component
  function handleTabClick(e) {

    const activePage = parseInt(e.target.getAttribute('data-tab-id'), 10);

    setActivePage(prevActivePage => {
      return activePage;
    });

  }

  //Used by the Navigation component
  function handleSearchIconClick(e) {

    setIsSearching(prevIsSearching => {

      if (!prevIsSearching) {
        return true;
      } else {
        document.getElementById('search-input').focus();
        document.getElementById('search-input').select();
        return prevIsSearching;
      }
    });
  }

  //Used by the Navigation component
  function handleCloseSearchIconClick(e) {
    setIsSearching(prevIsSearching => {
      return !prevIsSearching;
    });
  }

  //Used by the current component
  function handleChanges(value, name) {
    setFormData(prevFormData => {
      return {
        ...prevFormData,
        [name]: value,
      };
    });
  }

  //Used by the current component
  function handleRangeControlChanges(value, name) {
    setFormData(prevFormData => {
      return {
        ...prevFormData,
        [name]: value,
      };
    });
  }

  //Handle changes of the <Toggle> component
  function handleToggleChanges(e, name){

    setFormData(prevFormData => {

      return {
        ...prevFormData,
        [name]: e ? 1 : 0,
      };
    });

  }

  //Used to handle the changes to a React Select component
  function handleReactSelectChanges(e, name) {

    setFormData(prevFormData => {
      return {
        ...prevFormData,
        [name]: e.value,
      };
    });

  }

  //Used to handle the changes to a react-colorful component
  function handleReactColorfulChanges(name, value) {

    setFormData(prevFormData => {
      return {
        ...prevFormData,
        [name]: value,
      };
    });

  }

  //Used to handle the changes to a React Select Multiple component
  function handleReactSelectMultipleChanges(e, name) {

    let values = [];
    e.forEach(function(item, index){
      values.push(item.value);
    });

    setFormData(prevFormData => {
      return {
        ...prevFormData,
        [name]: values,
      };
    });

  }

  /**
   * Save the options by sending a request to the REST API endpoint provided by
   * the plugin.
   *
   * @param e
   */
  function handleSave(e) {

    //prevent the default submit event
    e.preventDefault();

    //get the "cardId" attribute of the clicked button
    const cardId = parseInt(e.target.getAttribute('cardId'), 10);

    let options = {};

    //create a each that iterates over the pages array
    settings.pages.forEach(page => {

      //create a each that iterates over the cards array
      page.cards.forEach((card, index) => {

        if (cardId === null || cardId === (index + 1)) {

          //create a each that iterates over the options array
          card.options.forEach(option => {

            //add the option to the options object
            options[option.name] = formData[option.name];

          });

        }

      });

    });

    wp.apiFetch({
      path: '/ultimate-markdown-pro/v1/options',
      method: 'POST',
      data: {
        ...options
      },
    }).then(data => {
      document.getElementById('notification-message').style.display = 'block';
      setTimeout(function() {
        document.getElementById('notification-message').style.display = 'none';
      }, 3000);
    });

  }

  return (


      <>

        <React.StrictMode>

          {
            dataAreLoaded ?

            <>

              <div className="daextrevop-body-container">

                <Navigation
                    activePage={activePage}
                    isSearching={isSearching}
                    searchInput={searchInput}
                    onTabClick={handleTabClick}
                    onSearchIconClick={handleSearchIconClick}
                    onCloseSearchIconClick={handleCloseSearchIconClick}
                    onHandleSearchInputChanges={handleSearchInputChanges}
                />

                <SettingsContainer
                    handleSave={handleSave}
                    handleChanges={handleChanges}
                    activePage={activePage}
                    formData={formData}
                    handleToggleChanges={handleToggleChanges}
                    handleReactSelectChanges={handleReactSelectChanges}
                    handleReactSelectMultipleChanges={handleReactSelectMultipleChanges}
                    handleReactColorfulChanges={handleReactColorfulChanges}
                    handleRangeControlChanges={handleRangeControlChanges}
                    isSearching={isSearching}
                    searchInput={searchInput}
                />

              </div>

              <DismissibleNotice/>

            </>
            :
            <LoadingScreen
                loadingDataMessage={__('Loading data...', 'ultimate-markdown-pro')}
            />
        }

        </React.StrictMode>

      </>

  );

};
export default App;