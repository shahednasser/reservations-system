import React, { Component } from 'react';
import ReactDOM from 'react-dom';

export default class App extends Component {
    render() {
        return (
            <div>
                <div className="d-sm-none d-block">
                    <select name="section">
                        <option name="reservation-requests"></option>
                    </select>
                </div>
                <div className="row">
                    <div className="col-sm-10 col-12">
                    </div>
                    <div className="col-sm-2 d-sm-block d-none">
                    </div>
                </div>
            </div>
        );
    }
}

if (document.getElementById('example')) {
    ReactDOM.render(<App />, document.getElementById('root'));
}
