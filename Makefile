
test: testfeature testunit

testfeature:
	vendor\bin\behat .\tests\

testunit:
	vendor\bin\phpunit .\tests\
