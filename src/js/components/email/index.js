// @flow

import type { State } from 'types';
import Email from './email';
import { connect } from 'react-redux';
import { setEmail } from 'actions';

const mapStateToProps = (state: State) => ({
  email: state.email
});

const actions = {
  setEmail
};

const connectRedux = connect(mapStateToProps, actions);

export default connectRedux(Email);
