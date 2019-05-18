Feature: Rotate images properly

  Scenario: Importing and properly rotating images
    Given I have an image where the top is up
    When I load the image
    Then the top should be up
