#!/usr/bin/env node
/**
 * Smoke-test: проверяет что фронт открывается и отдаёт валидный HTML.
 * Использование: node scripts/smoke-test.js https://your-app.vercel.app
 */
import https from 'https'
import http  from 'http'

const url = process.argv[2]

if (!url) {
  console.error('Usage: node scripts/smoke-test.js <url>')
  process.exit(1)
}

console.log(`Smoke-testing: ${url}`)

const client = url.startsWith('https://') ? https : http

const req = client.get(url, (res) => {
  const { statusCode } = res
  let body = ''

  res.setEncoding('utf8')
  res.on('data', chunk => (body += chunk))
  res.on('end', () => {
    const errors = []

    if (statusCode !== 200) {
      errors.push(`HTTP status ${statusCode} (expected 200)`)
    }
    if (!body.includes('<div id="root">')) {
      errors.push('React root element <div id="root"> not found')
    }
    if (!body.includes('NeverLands')) {
      errors.push('"NeverLands" not found in HTML')
    }

    if (errors.length) {
      console.error('FAIL:')
      errors.forEach(e => console.error('  ✗ ' + e))
      process.exit(1)
    }

    console.log('PASS:')
    console.log(`  ✓ Status 200`)
    console.log(`  ✓ React root found`)
    console.log(`  ✓ App title found`)
    process.exit(0)
  })
})

req.on('error', (err) => {
  console.error(`FAIL: Connection error — ${err.message}`)
  process.exit(1)
})

req.setTimeout(15_000, () => {
  console.error('FAIL: Request timed out (15s)')
  req.destroy()
  process.exit(1)
})
