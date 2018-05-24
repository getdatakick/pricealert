// @flow

import type { State } from 'types';
import Email from './email';
import { connect } from 'react-redux';
import { setEmail, onAgree } from 'actions';

const mapStateToProps = (state: State) => ({
  email: state.email
});

const actions = {
  setEmail,
  onAgree
};

const connectRedux = connect(mapStateToProps, actions);

export default connectRedux(Email);
