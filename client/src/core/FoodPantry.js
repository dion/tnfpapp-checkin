import React, { useState, useEffect, Fragment } from "react";
import { Redirect, Link, withRouter, useLocation } from "react-router-dom";
import Navigation from "./common/Navigation";
import Checkout from "./Checkout";
import Serving from "./Serving";
import CheckIn from "./CheckIn";
import { ClientProvider } from "./common/ClientContext";

const FoodPantry = (props) => {
  const [redirect, setRedirect] = useState(false);
  const activeTab = useLocation().search;
  const activeParam = new URLSearchParams(activeTab).get('active');
  const checkinTabActive = !activeParam ? 'active' : '';
  const servingTabActive = activeParam == 'serving' ? 'active' : '';
  const checkoutTabActive = activeParam == 'checkout' ? 'active' : '';
  const checkinContentActive = !activeParam ? 'show' : '';
  const servingContentActive = activeParam == 'serving' ? 'show' : '';
  const checkoutContentActive = activeParam == 'checkout' ? 'show' : '';

  useEffect(() => {
    console.log('activeParam', activeParam);

    if (sessionStorage.getItem("jwt")) {
    } else {
      setRedirect(true);
    }
  }, []);

  const doLogout = () => {
    sessionStorage.setItem("jwt", "");
    sessionStorage.clear();
    setRedirect(true);
  };

  if (redirect) {
    return <Redirect to="/" />;
  }

  return (
    <ClientProvider>
      <Fragment>
        <Navigation logoutFunction={doLogout} logoutLink={true} />
        <section id="tabs">
          <div className="container" style={{ maxWidth: "100%" }}>
            <div className="row">
              <div className="col-xs-12" style={{ width: "100%" }}>
                <nav>
                  <div
                    className="nav nav-tabs nav-fill"
                    id="nav-tab"
                    role="tablist"
                    style={{ backgroundColor: "white" }}
                  >
                    <a
                      className={`nav-item nav-link ${checkinTabActive}`}
                      id="nav-home-tab"
                      data-toggle="tab"
                      href="#nav-home"
                      role="tab"
                      aria-controls="nav-home"
                      aria-selected="true"
                    >
                      Check In
                    </a>
                    <a
                      className={`nav-item nav-link ${servingTabActive}`}
                      id="nav-profile-tab"
                      data-toggle="tab"
                      href="#nav-profile"
                      role="tab"
                      aria-controls="nav-profile"
                      aria-selected="false"
                    >
                      Serving
                    </a>
                    <a
                      className={`nav-item nav-link ${checkoutTabActive}`}
                      id="nav-about-tab"
                      data-toggle="tab"
                      href="#nav-about"
                      role="tab"
                      aria-controls="nav-about"
                      aria-selected="false"
                    >
                      Check Out
                    </a>
                  </div>
                </nav>
                <div
                  className="tab-content py-3 px-3 px-sm-0"
                  id="nav-tabContent"
                >
                  <div
                    className={`tab-pane fade ${checkinContentActive} ${checkinTabActive} outer-wrapper`}
                    id="nav-home"
                    role="tabpanel"
                    aria-labelledby="nav-home-tab"
                  >
                    <div className="table-wrapper-scroll-y my-custom-scrollbar">
                      <CheckIn place="Food pantry" />
                    </div>
                    <Link to="/searchclient" style={{ textDecoration: "none" }}>
                      <button
                        type="button"
                        className="btn btn-success btn-lg btn-block"
                      >
                        Check In
                      </button>
                    </Link>
                  </div>
                  <div
                    className={`tab-pane fade ${servingContentActive} ${servingTabActive} outer-wrapper`}
                    id="nav-profile"
                    role="tabpanel"
                    aria-labelledby="nav-profile-tab"
                  >
                    <div className="table-wrapper-scroll-y my-custom-scrollbar">
                      <Serving place="Food pantry" />
                    </div>
                  </div>
                  <div
                    className={`tab-pane fade ${checkoutContentActive} ${checkoutTabActive} outer-wrapper`}
                    id="nav-about"
                    role="tabpanel"
                    aria-labelledby="nav-about-tab"
                  >
                    <div className="table-wrapper-scroll-y my-custom-scrollbar">
                      <Checkout place="Food pantry" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </Fragment>
    </ClientProvider>
  );
};

export default withRouter(FoodPantry);
