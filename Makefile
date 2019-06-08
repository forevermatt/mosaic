
test: testfeature testunit

testfeature:
	vendor\bin\behat . --strict --append-snippets --stop-on-failure

testunit:
	vendor\bin\phpunit .\tests\
