Feature: packman
	In order to use a Baloo model
	As a developer
	I need to be able to manage Baloo models
	
Scenario: Load Baloo pack file
	Given I an empty database "baloo_test"
	And I have a file "baloo_test.json"
	When I 