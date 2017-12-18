// @flow

import type { Settings, Attribute, AttributeValues } from 'types';
import React from 'react';
import Slider from 'material-ui/Slider';
import { map } from 'ramda';
import { formatCurrency } from 'utils/format';
import { getCombination, getImage } from 'utils/product';

import AttributeSelector from './attribute';
import css from 'cleanslate.less';

type Props = {
  settings: Settings,
  attributes: AttributeValues,
  setAttribute: (number, number)=>void,
  slider: number,
  validCombination: boolean,
  setSlider: (number)=>void
}

class ProductView extends React.PureComponent<Props> {
  static displayName = 'Product';

  render() {
    const { settings, slider, setSlider, attributes, validCombination } = this.props;
    const { config, product, currency, translation } = settings;
    const combination = getCombination(product.combinations, attributes);

    const { minDiscount, step, showFullScale } = config;
    const limit = minDiscount || 0;
    const min = showFullScale ? 0 : limit;
    const max = 1;
    const outOfRange = (slider < limit || slider > max);
    const priceClazz = outOfRange ? css.error : null;

    return (
      <div className={css.product}>
        <div className={css.inner}>
          <div className={css.image}>
            <img src={getImage(product, combination)} />
            <div className={css.descr}>
              { this.renderMessage(settings, validCombination) }
            </div>
          </div>
          <div className={css.label}>
            { translation.alert_me_when_price_drops_to }
            <div className={priceClazz}>{ formatCurrency(product.price * slider, currency)}</div>
          </div>
          <Slider
            name="slider"
            min={min}
            max={max}
            step={step}
            value={slider}
            disabled={! validCombination}
            onChange={(e, val) => setSlider(val)} />
          <div className={css.groups}>
            { map(this.renderAttribute, product.attributes) }
          </div>
        </div>
      </div>
    );
  }

  renderMessage = (settings: Settings, validCombination: boolean) => {
    const { translation, product, currency } = settings;
    if (! validCombination) {
      return <span className={css.error}>{translation.combination_does_not_exists}</span>;
    }
    return translation.current_price + ' ' + formatCurrency(product.price, currency);
  }

  renderAttribute = (attribute: Attribute) => {
    const id = attribute.id;
    const { attributes, setAttribute } = this.props;
    return (
      <AttributeSelector
        key={id}
        selected={attributes[id]}
        attribute={attribute}
        setAttribute={setAttribute} />
    );
  }
}

export default ProductView;
