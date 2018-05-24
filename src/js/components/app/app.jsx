// @flow

import type { Step, Settings, AttributeValues } from 'types';
import React from 'react';
import Dialog from 'material-ui/Dialog';
import FlatButton from 'material-ui/FlatButton';
import Snackbar from 'material-ui/Snackbar';
import { validateEmail, validateSlider } from 'utils/validators';
import { getPrice, getCombination } from 'utils/product';
import { roundCurrency } from 'utils/format';
import css  from 'cleanslate.less';
import FixedLayout from './fixed-layout';
import Product from 'components/product';
import Email from 'components/email';

export type Props = {
  settings: Settings,
  show: boolean,
  setShow: (boolean)=>void,
  snackbar: ?string,
  setSnackbar: (?string)=>void,
  step: Step,
  setStep: (Step)=>void,
  useFixedLayout: boolean,
  slider: number,
  attributes: AttributeValues,
  email: string,
  agree: boolean,
  submit: ({})=>void
}

class App extends React.PureComponent<Props> {
  static displayName = 'App';

  render() {
    const { settings, snackbar, setSnackbar, show, setShow, step, useFixedLayout, email, agree } = this.props;
    const translation = settings.translation;
    const validCombination = this.validCombination();
    const valid = step === 'product' ? this.validateProduct(validCombination) : agree && validateEmail(email);
    const action = step === 'product' ? this.nextStep : this.submit;

    const Wrapper = useFixedLayout ? FixedLayout : Dialog;
    const style = useFixedLayout ? {} : { width: 700, maxWidth: 700 };
    const Buttons = (
      <div className={css.buttons}>
        <FlatButton
          label={translation.cancel}
          onTouchTap={x => setShow(false)} />
        <FlatButton
          secondary={true}
          label={translation.create_alert}
          disabled={!valid}
          onTouchTap={action} />
      </div>
    );

    return (
      <div className={css.cleanslate}>
        <Wrapper
          open={show}
          autoScrollBodyContent={true}
          onRequestClose={() => setShow(false)}
          contentStyle={style} >
          <div className={css.root}>
            { this.renderStep(step, valid, validCombination) }
            { Buttons }
          </div>
        </Wrapper>
        <Snackbar
          open={!! snackbar}
          message={snackbar || ""}
          autoHideDuration={2000}
          onRequestClose={() => setSnackbar(null)} />
      </div>
    );
  }

  renderStep = (step: Step, valid: boolean, validCombination: boolean) => {
    return step == 'product' ? this.renderProductStep(validCombination) : this.renderEmailStep(valid);
  }

  renderProductStep = (validCombination: boolean) => (
    <Product
      validCombination={validCombination}
      settings={this.props.settings} />
  );

  renderEmailStep = (valid: boolean) => (
    <Email
      valid={valid}
      settings={this.props.settings}
      onSubmit={this.submit} />
  );

  nextStep = () => {
    this.props.setStep('email');
  }

  validCombination = () => {
    const { settings, attributes } = this.props;
    const combinations = settings.product.combinations;
    const hasCombinations = combinations.length > 0;
    if (hasCombinations) {
      const combination = getCombination(combinations, attributes);
      if (! combination) {
        return false;
      }
    }
    return true;
  }

  validateProduct = (validCombination: boolean) => {
    const { slider, settings } = this.props;
    return validCombination && validateSlider(slider, settings);
  }

  submit = () => {
    const { submit, email, settings, attributes, slider } = this.props;
    const combination = getCombination(settings.product.combinations, attributes);
    const price = getPrice(settings.product, combination);
    submit({
      email,
      customer: settings.customer.id,
      product: settings.product.id,
      combination: combination ? combination.id : -1,
      price: roundCurrency(price * slider)
    });
  }
}

export default App;
