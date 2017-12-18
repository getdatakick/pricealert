// @flow

import type { State } from 'types';
import Product from './product';
import { connect } from 'react-redux';
import { setSlider, setAttribute } from 'actions';

const mapStateToProps = (state: State) => ({
  slider: state.slider,
  attributes: state.attributes
});

const actions = {
  setSlider,
  setAttribute
};

const connectRedux = connect(mapStateToProps, actions);

export default connectRedux(Product);
