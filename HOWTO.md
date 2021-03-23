# INSTRUCTION

1. Project requires http server to be run (I used wamp locally on windows and apache on AWS
2. OAUTH App must be set for existing GitHub account
3. Following ENV vars must be set:
  GH_CLIENT_ID // Client id related to existing oauth app 
  GH_CLIENT_SECRET // Client secret related to existing oauth app 
  GH_REPOSITORIES // repositories reletad to the github account
  GH_ACCOUNT // login of the github account for which auth app was created
  
  
 # TESTING
 1. Manually tested for repositories on my github account
 2. Unit tests: php [PROJECT_ROOT]/vendor/phpunit/phpunit/phpunit [PROJECT_ROOT]/src/tests/

