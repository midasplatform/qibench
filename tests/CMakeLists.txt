# functionality tests will be added by the subdirectories 
#add_subdirectory( models )
add_subdirectory( controllers )

# Style tests for code
add_midas_style_test( StyleQibenchControllers ${CMAKE_SOURCE_DIR}/modules/qibench/controllers )
add_midas_style_test( StyleQibenchComponents ${CMAKE_SOURCE_DIR}/modules/qibench/controllers/components )

add_midas_style_test( StyleQibenchModels ${CMAKE_SOURCE_DIR}/modules/qibench/models )
add_midas_style_test( StyleQibenchModelsBase ${CMAKE_SOURCE_DIR}/modules/qibench/models/base )
add_midas_style_test( StyleQibenchModelsDAO ${CMAKE_SOURCE_DIR}/modules/qibench/models/dao )
add_midas_style_test( StyleQibenchModelPDO ${CMAKE_SOURCE_DIR}/modules/qibench/models/pdo )

# Style tests for test code
add_midas_style_test( StyleTestsQibenchControllers ${CMAKE_SOURCE_DIR}/modules/qibench/tests/controllers )
add_midas_style_test( StyleTestsQibenchComponents ${CMAKE_SOURCE_DIR}/modules/qibench/tests/controllers/components )
add_midas_style_test( StyleTestsQibenchLibrary ${CMAKE_SOURCE_DIR}/modules/qibench/tests/library )
add_midas_style_test( StyleTestsQibenchModels ${CMAKE_SOURCE_DIR}/modules/qibench/tests/models )
add_midas_style_test( StyleTestsQibenchModelsBase ${CMAKE_SOURCE_DIR}/modules/qibench/tests/models/base )
