<?php
namespace forevermatt\mosaic\test;

use forevermatt\mosaic\SliceGrid;

class SliceGridTest extends \PHPUnit_Framework_TestCase
{
    const MAX_SLICES = 0;
    const MIN_SLICES = 1;
    const GUIDE_ASPECT_RATIO = 2;
    const DESIRED_SOURCE_ASPECT_RATIO = 3;
    const EXPECTED_NUM_COLUMNS = 4;
    const EXPECTED_NUM_ROWS = 5;
    
    private $testCases = array(
        
        // 3x3 square (exact, match min, match max)
        array( 9,  9, /*1:1*/ 1, /*1:1*/ 1,               3, 3),
        array(15,  9, /*1:1*/ 1, /*1:1*/ 1,               3, 3),
        array( 9,  5, /*1:1*/ 1, /*1:1*/ 1,               3, 3),
        
        // 4x4 square
        array(16, 16, /*1:1*/ 1, /*1:1*/ 1,               4, 4),
        array(24, 16, /*1:1*/ 1, /*1:1*/ 1,               4, 4),
        array(16, 10, /*1:1*/ 1, /*1:1*/ 1,               4, 4),
        
        // 8x4 double-wide
        array(32, 32, /*2:1*/ 2, /*1:1*/ 1,               8, 4),
        
        // 4:3 sources into a square guide image
        array(12, 12, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 3, 4),
        array(12, 10, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 3, 4),
        array(15, 10, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 3, 4),
        array(15, 12, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 3, 4),
        
        // More 4:3 sources into a square guide image
        array(47, 12, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 3, 4),
        array(48, 12, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 6, 8),
        array(48, 13, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 6, 8),
        
        // Way more 4:3 sources into a square guide image
        array(120000, 120000, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 300, 400),
        array(480000, 120000, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 600, 800),
        array(480000, 130000, /*1:1*/ 1, /*4:3*/ 1.3333333333333, 600, 800),
        
        // 4:3 sources into a double-wide guide image
        array(24, 24, /*2:1*/ 2, /*4:3*/ 1.3333333333333, 6, 4),
        
        // 3:4 sources into a square guide image
        array(12, 12, /*1:1*/ 1, /*3:4*/ 0.75,            4, 3),
        
        // 3:4 sources into a double-wide guide image
        array(24, 24, /*2:1*/ 2, /*3:4*/ 0.75,            8, 3),
        
        // 4:3 sources into a 4:3 guide image
        array( 4,  1, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 2, 2),
        array( 4,  4, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 2, 2),
        array( 8,  4, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 2, 2),
        array( 9,  4, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 3, 3),
        array( 9,  5, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 3, 3),
        array( 9,  9, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 3, 3),
        array(15,  9, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 3, 3),
        array(16,  9, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 4, 4),
        array(16, 10, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 4, 4),
        array(160000, 90000, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 400, 400),
        array(159999, 90000, /*4:3*/ 1.3333333333333, /*4:3*/ 1.3333333333333, 399, 399),
        
        // Odd scenario where maxNumColumns was ending up less than minNumColumns.
        array(1270, 1220, /*3:2*/ 1.5, /*4:3*/ 1.3333333333333, 37, 33),
    );
    
    public function testAllTestCases()
    {
        foreach ($this->testCases as $testCase) {
            try {
                $this->assertValidResults($testCase);
            } catch (\Exception $e) {
                $this->fail(
                    $e . PHP_EOL . $this->getTestCaseAsString($testCase)
                );
            }
        }
    }
    
    private function assertValidResults($testCase)
    {
        // Arrange: (see definition of $this->testCases)

        // Act:
        $sliceGrid = new SliceGrid(
            $testCase[self::MAX_SLICES],
            $testCase[self::MIN_SLICES],
            $testCase[self::GUIDE_ASPECT_RATIO],
            $testCase[self::DESIRED_SOURCE_ASPECT_RATIO]
        );

        // Assert:
        $actualNumColumns = $sliceGrid->getNumColumns();
        $actualNumRows = $sliceGrid->getNumRows();
        $this->assertEquals(
            $testCase[self::EXPECTED_NUM_COLUMNS],
            $actualNumColumns,
            sprintf(
                'Wrong number of columns (%s) produced by test case: %s',
                $actualNumColumns,
                $this->getTestCaseAsString($testCase)
            )
        );
        $this->assertEquals(
            $testCase[self::EXPECTED_NUM_ROWS],
            $actualNumRows,
            sprintf(
                'Wrong number of rows (%s) produced by test case: %s',
                $actualNumRows,
                $this->getTestCaseAsString($testCase)
            )
        );
        $actualNumSlices = $actualNumColumns * $actualNumRows;
        $this->assertLessThanOrEqual(
            $testCase[self::MAX_SLICES],
            $actualNumSlices,
            sprintf(
                'Too many slices (%s) produced by test case: %s',
                $actualNumSlices,
                $this->getTestCaseAsString($testCase)
            )
        );
        $this->assertGreaterThanOrEqual(
            $testCase[self::MIN_SLICES],
            $actualNumSlices,
            sprintf(
                'Too few slices (%s) produced by test case: %s',
                $actualNumSlices,
                $this->getTestCaseAsString($testCase)
            )
        );
    }
    
    private function getTestCaseAsString($testCase)
    {
        return var_export(array(
            'maxSlices' => $testCase[self::MAX_SLICES],
            'minSlices' => $testCase[self::MIN_SLICES],
            'guideAspectRatio' => $testCase[self::GUIDE_ASPECT_RATIO],
            'desiredSourceAspectRatio' => $testCase[self::DESIRED_SOURCE_ASPECT_RATIO],
            'expectedNumColumns' => $testCase[self::EXPECTED_NUM_COLUMNS],
            'expectedNumRows' => $testCase[self::EXPECTED_NUM_ROWS],
        ), true);
    }
}
