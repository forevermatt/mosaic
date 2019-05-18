
test: testfeature testunit

testfeature:
	vendor\bin\behat .

testunit:
	vendor\bin\phpunit .\tests\
