// @flow

import type { Attribute, AttributeValue } from 'types';
import React from 'react';
import SelectField from 'material-ui/SelectField';
import MenuItem from 'material-ui/MenuItem';

type Props = {
  attribute: Attribute,
  selected: number,
  setAttribute: (number, number)=>void
};

class AttributeSelector extends React.PureComponent<Props> {
  static displayName = 'AttributeSelector';

  render() {
    const { attribute, selected } = this.props;
    const { values, name } = attribute;
    return (
      <SelectField
        value={selected}
        onChange={this.setAttribute}
        fullWidth={true}
        floatingLabelText={name} >
        { values.map(this.renderMenuItem) }
      </SelectField>
    );

  }

  renderMenuItem = (value: AttributeValue) => (
    <MenuItem
      key={value.id}
      value={value.id}
      primaryText={value.name} />
  );

  setAttribute = (e: any, index: number, value: number) => {
    const { attribute, setAttribute } = this.props;
    setAttribute(attribute.id, value);
  }
}

export default AttributeSelector;
