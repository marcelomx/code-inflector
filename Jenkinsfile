pipeline {
  agent any
  stages {
    stage('Build') {
      parallel {
        stage('Build') {
          steps {
            sh 'echo "Hello world"'
            sleep 1
            echo 'Printed message'
          }
        }
        stage('Building Paralell') {
          steps {
            echo 'Step Paralell'
          }
        }
      }
    }
    stage('Artifactory') {
      steps {
        archiveArtifacts(artifacts: '**/**', allowEmptyArchive: true, fingerprint: true, onlyIfSuccessful: true)
      }
    }
  }
}