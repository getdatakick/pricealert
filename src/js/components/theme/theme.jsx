// @flow
import React from 'react';
import { merge } from 'ramda';
import getMuiTheme from 'material-ui/styles/getMuiTheme';
import lightTheme from 'material-ui/styles/baseThemes/lightBaseTheme';
import darkTheme from 'material-ui/styles/baseThemes/darkBaseTheme';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import App from 'components/app';
import type { Settings } from 'types';


type Props = {
  settings: Settings
};

class Theme extends React.PureComponent<Props> {
  static displayName = 'Theme';

  render() {
    const { settings } = this.props;
    return (
      <MuiThemeProvider muiTheme={getTheme(settings)}>
        <App settings={settings} />
      </MuiThemeProvider>
    );
  }
}

const getTheme = (settings: Settings) => {
  const theme = settings.theme == 'dark' ? darkTheme : lightTheme;
  const baseIndex = 9999;
  return getMuiTheme(merge(theme, {
    zIndex: {
      mobileStepper: baseIndex+900,
      menu: baseIndex+1000,
      appBar: baseIndex+1100,
      drawerOverlay: baseIndex+1200,
      navDrawer: baseIndex+1300,
      dialogOverlay: baseIndex+1400,
      dialog: baseIndex+1500,
      layer: baseIndex+2000,
      popover: baseIndex+2100,
      snackbar: baseIndex+2900,
      tooltip: baseIndex+3000,
    }
  }));
};

export default Theme;
