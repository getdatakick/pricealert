// @flow

import type { Settings } from 'types';
import React from 'react';
import TextField from 'material-ui/TextField';
import Icon from 'material-ui/svg-icons/communication/email';
import styles from 'cleanslate.less';

type Props = {
  email: string,
  valid: boolean,
  settings: Settings,
  setEmail: (string)=>void,
  onSubmit: ()=>void
}

class Email extends React.PureComponent<Props> {
  static displayName = 'Email';

  render() {
    const { email, valid, settings, onSubmit, setEmail } = this.props;
    const translation = settings.translation;
    const text = translation.your_email_address;
    return (
      <div className={styles.email}>
        <div>
          <Icon className={styles.icon} style={{width: null, height: null}} color="#999"/>
        </div>
        <div>
          <TextField
            ref={(e) => e && e.focus()}
            value={email}
            fullWidth={true}
            floatingLabelText={text}
            hintText={text}
            onKeyPress={e => valid && "Enter" == e.key && onSubmit()}
            onChange={e => setEmail(e.target.value)} />
        </div>
      </div>
    );
  }
}

export default Email;
