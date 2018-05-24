// @flow

import type { State } from 'types';
import App from './app';
import { connect } from 'react-redux';
import { submit, setShow, setStep, setSnackbar } from 'actions';

const mapStateToProps = (state: State) => ({
  show: state.show,
  step: state.step,
  snackbar: state.message,
  useFixedLayout: state.width < 700,
  slider: state.slider,
  attributes: state.attributes,
  email: state.email,
  agree: state.agree
});

const actions = {
  setShow,
  setSnackbar,
  setStep,
  submit
};

const connectRedux = connect(mapStateToProps, actions);

export default connectRedux(App);
