<?php
namespace WebApp\Controller;

/* === DEVELOPER BEGIN */
/**
 *  @desc {{desc}}
 *
 *  @status 0
 */
/* === DEVELOPER END */

class {{classname}} {
{{deps_members}}
  
  public function __construct({{deps_list}}) {
{{deps_assign}}
  }
  
  public function __invoke($request, $response, $args) {  
    $action_result = $this->doAction($request, $args);
    $redir = $this->router->pathFor($action_result["route_to"]);
    if (isset($action_result["qs"])) {
      $redir .= "?" . http_build_query($action_result["qs"]);
    }
    return $response->withRedirect($redir);
  }
  
  /* === DEVELOPER BEGIN */
  /**
   *  @return string ["route_to"] The route used for redirect
   *  @return optional array ["qs"] The query string attributes
   */
  private function doAction($request, $args) {
    // here you can read post values from request
    // use them in a model "get" function to retrieve data
    // and set a redirect/qs based on the model result
    //  e.g.
    // $email = $request->getParsedBody()["email"];
    // $password = $request->getParsedBody()["password"];
    // $user = $this->User_by_username_passwordModel->get($email, $password);
    // if (count($user) != 1) {
    //   return $this->app->redirDescriptor("MESSAGE", "login", "err");
    // }
    //
    // $this->auth->create_user_session($user);
    // return [
    //  "route_to" => "PROFILE"
    // ];
  }
  /* === DEVELOPER END */
}